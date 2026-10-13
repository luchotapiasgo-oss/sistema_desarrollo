<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\OpcionesFormulario;
use App\Http\Requests\ActividadRequest;
use App\Models\Actividad;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActividadController extends Controller
{
    use AuthorizesRequests, OpcionesFormulario;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Actividad::class);

        $actividades = Actividad::with('oficina')
            ->when($request->filled('buscar'), fn ($q) => $q->where('titulo', 'like', '%'.$request->input('buscar').'%'))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('prioridad'), fn ($q) => $q->dePrioridad($request->input('prioridad')))
            ->when($request->filled('oficina_id'), fn ($q) => $q->where('oficina_id', $request->integer('oficina_id')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha_inicio', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha_inicio', '<=', $request->date('hasta')))
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('Actividad.index', [
            'actividades' => $actividades,
            'oficinas' => $this->opcionesOficinas(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Actividad::class);

        return view('Actividad.create', [
            'actividad' => new Actividad(['prioridad' => 'media', 'estado' => 'pendiente']),
            'oficinas' => $this->opcionesOficinas(),
        ]);
    }

    public function store(ActividadRequest $request): RedirectResponse
    {
        $actividad = Actividad::create($request->validated());

        return redirect()->route('actividades.show', $actividad)
            ->with('exito', 'Actividad registrada correctamente.');
    }

    public function show(Actividad $actividad): View
    {
        $this->authorize('view', $actividad);

        $actividad->load(['oficina', 'alertas']);

        return view('Actividad.show', compact('actividad'));
    }

    public function edit(Actividad $actividad): View
    {
        $this->authorize('update', $actividad);

        return view('Actividad.edit', [
            'actividad' => $actividad,
            'oficinas' => $this->opcionesOficinas(),
        ]);
    }

    public function update(ActividadRequest $request, Actividad $actividad): RedirectResponse
    {
        $actividad->update($request->validated());

        return redirect()->route('actividades.show', $actividad)
            ->with('exito', 'Actividad actualizada correctamente.');
    }

    public function destroy(Actividad $actividad): RedirectResponse
    {
        $this->authorize('delete', $actividad);

        $actividad->delete();

        return redirect()->route('actividades.index')
            ->with('exito', 'Actividad eliminada.');
    }
}
