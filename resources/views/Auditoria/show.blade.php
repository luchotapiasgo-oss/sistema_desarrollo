@extends('layouts.app')

@section('titulo', 'Registro de auditoría #'.$auditoria->id)

@section('contenido')
    @php
        $mostrar = fn ($valor) => match (true) {
            $valor === null => '—',
            is_bool($valor) => $valor ? 'sí' : 'no',
            is_array($valor) => json_encode($valor, JSON_UNESCAPED_UNICODE),
            default => (string) $valor,
        };
        $anterior = $auditoria->valor_anterior ?? [];
        $nuevo = $auditoria->valor_nuevo ?? [];
    @endphp

    <a href="{{ route('auditorias.index') }}" class="mb-4 inline-block text-sm text-slate-500 hover:text-slate-800">&larr; Auditoría</a>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-5">
            <div><dt class="text-slate-500">Fecha</dt><dd class="font-medium">{{ $auditoria->created_at?->format('d/m/Y H:i:s') }}</dd></div>
            <div><dt class="text-slate-500">Usuario</dt><dd class="font-medium">{{ $auditoria->usuario?->nombre_usuario ?? 'Sistema' }}</dd></div>
            <div><dt class="text-slate-500">Acción</dt><dd><x-badge :valor="$auditoria->accion" /></dd></div>
            <div><dt class="text-slate-500">Tabla / registro</dt><dd class="font-mono text-xs font-medium">{{ $auditoria->tabla_afectada }} #{{ $auditoria->registro_id }}</dd></div>
            <div><dt class="text-slate-500">IP</dt><dd class="font-medium">{{ $auditoria->direccion_ip ?? '—' }}</dd></div>
        </dl>
    </div>

    <section class="mt-6 overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Campo</th>
                    <th class="px-4 py-3">Valor anterior</th>
                    <th class="px-4 py-3">Valor nuevo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($campos as $campo)
                    @php($cambio = $auditoria->accion === 'editar' && array_key_exists($campo, $nuevo))
                    <tr @class(['bg-amber-50' => $cambio])>
                        <td class="px-4 py-2 font-mono text-xs">{{ $campo }}</td>
                        <td class="break-all px-4 py-2 text-slate-600">{{ array_key_exists($campo, $anterior) ? $mostrar($anterior[$campo]) : '' }}</td>
                        <td class="break-all px-4 py-2">{{ array_key_exists($campo, $nuevo) ? $mostrar($nuevo[$campo]) : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">El registro no guardó valores.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    @if ($auditoria->accion === 'editar')
        <p class="mt-2 text-xs text-slate-500">En las ediciones se resaltan los campos modificados.</p>
    @endif
@endsection
