@extends('layouts.app')

@section('titulo', 'Auditoría')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $auditorias->total() }} registro(s). La auditoría es de solo lectura.</p>
        @can('export', App\Models\Auditoria::class)
            <a href="{{ route('auditorias.exportar', request()->query()) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Exportar CSV</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6">
        <x-form.select name="tabla" label="Tabla" :options="$tablas->combine($tablas)" :value="request('tabla')" placeholder="Todas" />
        <x-form.select name="accion" label="Acción" :options="array_combine($acciones, array_map('ucfirst', $acciones))" :value="request('accion')" placeholder="Todas" />
        <x-form.select name="usuario_id" label="Usuario" :options="$usuarios" :value="request('usuario_id')" placeholder="Todos" />
        <x-form.input type="number" name="registro_id" label="ID de registro" :value="request('registro_id')" min="1" />
        <x-form.input type="date" name="desde" label="Desde" :value="request('desde')" />
        <x-form.input type="date" name="hasta" label="Hasta" :value="request('hasta')" />
        <div class="flex items-end gap-2 lg:col-span-6">
            <button class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">Filtrar</button>
            <a href="{{ route('auditorias.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">Acción</th>
                    <th class="px-4 py-3">Tabla</th>
                    <th class="px-4 py-3">Registro</th>
                    <th class="px-4 py-3">Campos</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($auditorias as $auditoria)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3">
                            <a href="{{ route('auditorias.show', $auditoria) }}" class="font-medium hover:underline">{{ $auditoria->created_at?->format('d/m/Y H:i:s') }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $auditoria->usuario?->nombre_usuario ?? 'Sistema' }}</td>
                        <td class="px-4 py-3"><x-badge :valor="$auditoria->accion" /></td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $auditoria->tabla_afectada }}</td>
                        <td class="px-4 py-3">#{{ $auditoria->registro_id }}</td>
                        <td class="max-w-xs truncate px-4 py-3 text-xs text-slate-500">
                            {{ implode(', ', array_keys($auditoria->valor_nuevo ?? $auditoria->valor_anterior ?? [])) }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $auditoria->direccion_ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay registros de auditoría que coincidan con el filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $auditorias->links() }}</div>
@endsection
