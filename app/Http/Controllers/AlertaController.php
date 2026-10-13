<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertaRequest;
use App\Models\Alerta;
use App\Models\Usuario;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AlertaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Quien tiene alertas.ver ve todas; el resto, solo las propias.
     */
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $verTodas = $usuario->can('viewAny', Alerta::class);

        $alertas = Alerta::with(['usuario', 'alertable'])
            ->when(! $verTodas || $request->boolean('mias'), fn ($q) => $q->deUsuario($usuario->id))
            ->when($request->boolean('vencidas'), fn ($q) => $q->vencidas())
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('prioridad'), fn ($q) => $q->dePrioridad($request->input('prioridad')))
            ->when($request->filled('alertable_tipo'), fn ($q) => $q->where('alertable_tipo', Alerta::ALERTABLES[$request->input('alertable_tipo')] ?? null))
            ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 WHEN estado = 'leida' THEN 1 ELSE 2 END")
            ->orderByDesc('fecha_generacion')
            ->paginate(15)
            ->withQueryString();

        return view('Alerta.index', compact('alertas', 'verTodas'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Alerta::class);

        $alerta = new Alerta([
            'prioridad' => 'media',
            'estado' => 'pendiente',
            'fecha_generacion' => now(),
            'alertable_id' => $request->integer('alertable_id') ?: null,
        ]);
        // Se precarga desde las fichas de proyecto, actividad o incidencia.
        $alerta->alertable_tipo = Alerta::ALERTABLES[$request->input('alertable_tipo')] ?? null;

        return view('Alerta.create', [
            'alerta' => $alerta,
            'usuarios' => $this->opcionesUsuarios(),
        ]);
    }

    public function store(AlertaRequest $request): RedirectResponse
    {
        $alerta = Alerta::create($request->datosAlerta());

        return redirect()->route('alertas.show', $alerta)
            ->with('exito', 'Alerta registrada correctamente.');
    }

    /**
     * Si la abre su destinatario, la alerta queda marcada como leida.
     */
    public function show(Request $request, Alerta $alerta): View
    {
        $this->authorize('view', $alerta);

        if ($alerta->usuario_id === $request->user()->id) {
            $alerta->marcarLeida();
        }

        $alerta->load(['usuario', 'alertable']);

        return view('Alerta.show', compact('alerta'));
    }

    public function edit(Alerta $alerta): View
    {
        $this->authorize('update', $alerta);

        return view('Alerta.edit', [
            'alerta' => $alerta,
            'usuarios' => $this->opcionesUsuarios(),
        ]);
    }

    public function update(AlertaRequest $request, Alerta $alerta): RedirectResponse
    {
        $alerta->update($request->datosAlerta($alerta));

        return redirect()->route('alertas.show', $alerta)
            ->with('exito', 'Alerta actualizada correctamente.');
    }

    public function atender(Alerta $alerta): RedirectResponse
    {
        $this->authorize('atender', $alerta);

        $alerta->marcarAtendida();

        return back()->with('exito', 'Alerta marcada como atendida.');
    }

    public function destroy(Alerta $alerta): RedirectResponse
    {
        $this->authorize('delete', $alerta);

        $alerta->delete();

        return redirect()->route('alertas.index')
            ->with('exito', 'Alerta eliminada.');
    }

    /**
     * @return Collection<int, string>
     */
    private function opcionesUsuarios(): Collection
    {
        return Usuario::where('activo', true)->orderBy('nombre_usuario')->pluck('nombre_usuario', 'id');
    }
}
