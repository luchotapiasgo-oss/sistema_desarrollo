<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Consulta de la auditoria. Es de solo lectura: los registros los escribe
 * el trait RegistraAuditoria.
 */
class AuditoriaController extends Controller
{
    use AuthorizesRequests;

    const ACCIONES = ['crear', 'editar', 'eliminar', 'restaurar'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Auditoria::class);

        return view('Auditoria.index', [
            'auditorias' => $this->filtrar($request)
                ->with('usuario')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString(),
            'tablas' => Auditoria::query()->distinct()->orderBy('tabla_afectada')->pluck('tabla_afectada'),
            'usuarios' => Usuario::orderBy('nombre_usuario')->pluck('nombre_usuario', 'id'),
            'acciones' => self::ACCIONES,
        ]);
    }

    public function show(Auditoria $auditoria): View
    {
        $this->authorize('view', $auditoria);

        $auditoria->load('usuario');

        $campos = collect(array_keys(($auditoria->valor_anterior ?? []) + ($auditoria->valor_nuevo ?? [])));

        return view('Auditoria.show', compact('auditoria', 'campos'));
    }

    public function exportar(Request $request): StreamedResponse
    {
        $this->authorize('export', Auditoria::class);

        $consulta = $this->filtrar($request)->with('usuario')->orderBy('created_at');

        return response()->streamDownload(function () use ($consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Fecha', 'Usuario', 'Accion', 'Tabla', 'Registro', 'Valor anterior', 'Valor nuevo', 'IP'], ';');

            $consulta->chunk(500, function (Collection $auditorias) use ($salida) {
                foreach ($auditorias as $a) {
                    fputcsv($salida, [
                        $a->created_at?->format('d/m/Y H:i:s'),
                        $a->usuario?->nombre_usuario ?? 'Sistema',
                        $a->accion,
                        $a->tabla_afectada,
                        $a->registro_id,
                        $a->valor_anterior ? json_encode($a->valor_anterior, JSON_UNESCAPED_UNICODE) : '',
                        $a->valor_nuevo ? json_encode($a->valor_nuevo, JSON_UNESCAPED_UNICODE) : '',
                        $a->direccion_ip,
                    ], ';');
                }
            });

            fclose($salida);
        }, 'auditoria_'.now()->format('Ymd_His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtrar(Request $request): Builder
    {
        return Auditoria::query()
            ->when($request->filled('tabla'), fn ($q) => $q->where('tabla_afectada', $request->input('tabla')))
            ->when($request->filled('accion'), fn ($q) => $q->where('accion', $request->input('accion')))
            ->when($request->filled('usuario_id'), fn ($q) => $q->where('usuario_id', $request->integer('usuario_id')))
            ->when($request->filled('registro_id'), fn ($q) => $q->where('registro_id', $request->integer('registro_id')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('hasta')));
    }
}
