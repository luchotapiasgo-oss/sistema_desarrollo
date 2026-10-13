<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\OpcionesFormulario;
use App\Http\Requests\ProyectoRequest;
use App\Models\Proyecto;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProyectoController extends Controller
{
    use AuthorizesRequests, OpcionesFormulario;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Proyecto::class);

        $proyectos = Proyecto::with(['oficina', 'responsable.persona'])
            ->withCount('integrantes')
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $termino = '%'.$request->input('buscar').'%';
                $q->where(fn ($q) => $q->where('nombre', 'like', $termino)->orWhere('codigo', 'like', $termino));
            })
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('prioridad'), fn ($q) => $q->dePrioridad($request->input('prioridad')))
            ->when($request->filled('oficina_id'), fn ($q) => $q->where('oficina_id', $request->integer('oficina_id')))
            ->when($request->filled('responsable_id'), fn ($q) => $q->where('responsable_id', $request->integer('responsable_id')))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('Proyecto.index', [
            'proyectos' => $proyectos,
            'oficinas' => $this->opcionesOficinas(),
            'policias' => $this->opcionesPolicias(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Proyecto::class);

        return view('Proyecto.create', [
            'proyecto' => new Proyecto(['prioridad' => 'media', 'estado' => 'planificado', 'porcentaje_avance' => 0]),
            'oficinas' => $this->opcionesOficinas(),
            'policias' => $this->opcionesPolicias(),
        ]);
    }

    public function store(ProyectoRequest $request): RedirectResponse
    {
        $proyecto = Proyecto::create($request->validated());

        return redirect()->route('proyectos.show', $proyecto)
            ->with('exito', 'Proyecto registrado correctamente.');
    }

    public function show(Proyecto $proyecto): View
    {
        $this->authorize('view', $proyecto);

        $proyecto->load([
            'oficina',
            'responsable.persona',
            'integrantes.policia.persona',
            'movimientos' => fn ($q) => $q->orderByDesc('fecha'),
            'alertas',
        ]);

        // Los movimientos anulados no suman al balance del proyecto.
        $vigentes = $proyecto->movimientos->where('estado', '!=', 'anulado');
        $ingresos = (float) $vigentes->where('tipo', 'ingreso')->sum('monto');
        $egresos = (float) $vigentes->where('tipo', 'egreso')->sum('monto');

        $idsIntegrantes = $proyecto->integrantes->pluck('policia_id');

        return view('Proyecto.show', [
            'proyecto' => $proyecto,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'policiasDisponibles' => $this->opcionesPolicias()->except($idsIntegrantes->all()),
        ]);
    }

    public function edit(Proyecto $proyecto): View
    {
        $this->authorize('update', $proyecto);

        return view('Proyecto.edit', [
            'proyecto' => $proyecto,
            'oficinas' => $this->opcionesOficinas(),
            'policias' => $this->opcionesPolicias(),
        ]);
    }

    public function update(ProyectoRequest $request, Proyecto $proyecto): RedirectResponse
    {
        $proyecto->update($request->validated());

        return redirect()->route('proyectos.show', $proyecto)
            ->with('exito', 'Proyecto actualizado correctamente.');
    }

    public function destroy(Proyecto $proyecto): RedirectResponse
    {
        $this->authorize('delete', $proyecto);

        // Los integrantes se borran en cascada; los movimientos economicos
        // quedan con proyecto_id en NULL (nullOnDelete).
        $proyecto->delete();

        return redirect()->route('proyectos.index')
            ->with('exito', 'Proyecto eliminado.');
    }
}
