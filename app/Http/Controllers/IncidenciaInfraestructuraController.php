<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\OpcionesFormulario;
use App\Http\Requests\IncidenciaInfraestructuraRequest;
use App\Http\Requests\ResolverIncidenciaRequest;
use App\Models\IncidenciaInfraestructura;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidenciaInfraestructuraController extends Controller
{
    use AuthorizesRequests, OpcionesFormulario;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IncidenciaInfraestructura::class);

        $filtradas = IncidenciaInfraestructura::query()
            ->when($request->filled('buscar'), fn ($q) => $q->where('titulo', 'like', '%'.$request->input('buscar').'%'))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->input('tipo')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('prioridad'), fn ($q) => $q->dePrioridad($request->input('prioridad')))
            ->when($request->filled('oficina_id'), fn ($q) => $q->where('oficina_id', $request->integer('oficina_id')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha_reporte', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha_reporte', '<=', $request->date('hasta')));

        return view('IncidenciaInfraestructura.index', [
            'incidencias' => (clone $filtradas)->with(['oficina', 'responsable.persona'])
                ->orderByDesc('fecha_reporte')
                ->paginate(15)
                ->withQueryString(),
            'costoTotal' => (float) (clone $filtradas)->sum('costo'),
            'oficinas' => $this->opcionesOficinas(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', IncidenciaInfraestructura::class);

        return view('IncidenciaInfraestructura.create', [
            'incidencia' => new IncidenciaInfraestructura([
                'prioridad' => 'media',
                'estado' => 'pendiente',
                'fecha_reporte' => now(),
            ]),
            'oficinas' => $this->opcionesOficinas(),
            'policias' => $this->opcionesPolicias(),
        ]);
    }

    public function store(IncidenciaInfraestructuraRequest $request): RedirectResponse
    {
        $incidencia = IncidenciaInfraestructura::create($request->validated());

        return redirect()->route('incidencias.show', $incidencia)
            ->with('exito', 'Incidencia registrada correctamente.');
    }

    public function show(IncidenciaInfraestructura $incidencia): View
    {
        $this->authorize('view', $incidencia);

        $incidencia->load(['oficina', 'responsable.persona', 'alertas']);

        return view('IncidenciaInfraestructura.show', compact('incidencia'));
    }

    public function edit(IncidenciaInfraestructura $incidencia): View
    {
        $this->authorize('update', $incidencia);

        return view('IncidenciaInfraestructura.edit', [
            'incidencia' => $incidencia,
            'oficinas' => $this->opcionesOficinas(),
            'policias' => $this->opcionesPolicias(),
        ]);
    }

    public function update(IncidenciaInfraestructuraRequest $request, IncidenciaInfraestructura $incidencia): RedirectResponse
    {
        $datos = $request->validated();

        // Si se reabre la incidencia, deja de tener fecha de resolucion.
        if ($datos['estado'] !== 'resuelta') {
            $datos['fecha_resolucion'] = null;
        }

        $incidencia->update($datos);

        return redirect()->route('incidencias.show', $incidencia)
            ->with('exito', 'Incidencia actualizada correctamente.');
    }

    /**
     * Cierre rapido desde la ficha: estado, fecha de resolucion y costo final.
     */
    public function resolver(ResolverIncidenciaRequest $request, IncidenciaInfraestructura $incidencia): RedirectResponse
    {
        $incidencia->update([
            'estado' => 'resuelta',
            'fecha_resolucion' => $request->date('fecha_resolucion'),
            'costo' => $request->filled('costo') ? $request->input('costo') : $incidencia->costo,
            // Las observaciones de cierre se agregan a las existentes, no las reemplazan.
            'observaciones' => $request->filled('observaciones')
                ? trim($incidencia->observaciones."\n\nCierre: ".$request->input('observaciones'))
                : $incidencia->observaciones,
        ]);

        return redirect()->route('incidencias.show', $incidencia)
            ->with('exito', 'Incidencia marcada como resuelta.');
    }

    public function destroy(IncidenciaInfraestructura $incidencia): RedirectResponse
    {
        $this->authorize('delete', $incidencia);

        $incidencia->delete();

        return redirect()->route('incidencias.index')
            ->with('exito', 'Incidencia eliminada.');
    }
}
