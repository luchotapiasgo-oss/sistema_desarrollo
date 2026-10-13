<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\OpcionesFormulario;
use App\Http\Requests\MovimientoEconomicoRequest;
use App\Models\MovimientoEconomico;
use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MovimientoEconomicoController extends Controller
{
    use AuthorizesRequests, OpcionesFormulario;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MovimientoEconomico::class);

        $filtrados = $this->filtrar($request);

        // Totales sobre todo el filtro (no solo la pagina), sin anulados.
        $totales = (clone $filtrados)
            ->where('estado', '!=', 'anulado')
            ->selectRaw('tipo, SUM(monto) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        return view('MovimientoEconomico.index', [
            'movimientos' => (clone $filtrados)->with(['responsable.persona', 'proyecto'])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString(),
            'ingresos' => (float) ($totales['ingreso'] ?? 0),
            'egresos' => (float) ($totales['egreso'] ?? 0),
            'proyectos' => $this->opcionesProyectos(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', MovimientoEconomico::class);

        return view('MovimientoEconomico.create', [
            'movimiento' => new MovimientoEconomico([
                'tipo' => 'egreso',
                'estado' => 'registrado',
                'fecha' => today(),
                'proyecto_id' => $request->integer('proyecto_id') ?: null,
            ]),
            'policias' => $this->opcionesPolicias(),
            'proyectos' => $this->opcionesProyectos(),
        ]);
    }

    public function store(MovimientoEconomicoRequest $request): RedirectResponse
    {
        $movimiento = MovimientoEconomico::create($request->validated());

        return redirect()->route('movimientos.show', $movimiento)
            ->with('exito', 'Movimiento registrado correctamente.');
    }

    public function show(MovimientoEconomico $movimiento): View
    {
        $this->authorize('view', $movimiento);

        $movimiento->load(['responsable.persona', 'proyecto']);

        return view('MovimientoEconomico.show', compact('movimiento'));
    }

    public function edit(MovimientoEconomico $movimiento): View
    {
        $this->authorize('update', $movimiento);

        return view('MovimientoEconomico.edit', [
            'movimiento' => $movimiento,
            'policias' => $this->opcionesPolicias(),
            'proyectos' => $this->opcionesProyectos(),
        ]);
    }

    public function update(MovimientoEconomicoRequest $request, MovimientoEconomico $movimiento): RedirectResponse
    {
        $movimiento->update($request->validated());

        return redirect()->route('movimientos.show', $movimiento)
            ->with('exito', 'Movimiento actualizado correctamente.');
    }

    public function destroy(MovimientoEconomico $movimiento): RedirectResponse
    {
        $this->authorize('delete', $movimiento);

        $movimiento->delete();

        return redirect()->route('movimientos.index')
            ->with('exito', 'Movimiento eliminado.');
    }

    /**
     * Exporta a CSV los movimientos que coinciden con los filtros del listado.
     */
    public function exportar(Request $request): StreamedResponse
    {
        $this->authorize('export', MovimientoEconomico::class);

        $consulta = $this->filtrar($request)->with(['responsable.persona', 'proyecto'])->orderBy('fecha');

        return response()->streamDownload(function () use ($consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM para que Excel lea UTF-8
            fputcsv($salida, ['Fecha', 'Tipo', 'Categoria', 'Concepto', 'Proveedor', 'Monto', 'Proyecto', 'Responsable', 'Comprobante', 'Estado'], ';');

            $consulta->chunk(500, function (Collection $movimientos) use ($salida) {
                foreach ($movimientos as $m) {
                    fputcsv($salida, [
                        $m->fecha->format('d/m/Y'),
                        MovimientoEconomico::TIPOS[$m->tipo] ?? $m->tipo,
                        $m->categoria,
                        $m->concepto,
                        $m->proveedor,
                        number_format((float) $m->monto, 2, ',', ''),
                        $m->proyecto?->codigo,
                        $m->responsable?->nombre_completo,
                        $m->comprobante,
                        MovimientoEconomico::ESTADOS[$m->estado] ?? $m->estado,
                    ], ';');
                }
            });

            fclose($salida);
        }, 'movimientos_'.now()->format('Ymd_His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtrar(Request $request): Builder
    {
        return MovimientoEconomico::query()
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $termino = '%'.$request->input('buscar').'%';
                $q->where(fn ($q) => $q->where('concepto', 'like', $termino)
                    ->orWhere('proveedor', 'like', $termino)
                    ->orWhere('comprobante', 'like', $termino));
            })
            ->when($request->filled('tipo'), fn ($q) => $q->deTipo($request->input('tipo')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->input('categoria')))
            ->when($request->filled('proyecto_id'), fn ($q) => $q->where('proyecto_id', $request->integer('proyecto_id')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->date('hasta')));
    }

    /**
     * @return Collection<int, string>
     */
    private function opcionesProyectos(): Collection
    {
        return Proyecto::orderBy('codigo')->get(['id', 'codigo', 'nombre'])
            ->mapWithKeys(fn (Proyecto $p) => [$p->id => $p->codigo.' - '.$p->nombre]);
    }
}
