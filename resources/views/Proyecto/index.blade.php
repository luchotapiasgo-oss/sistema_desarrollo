@extends('layouts.app')

@section('titulo', 'Proyectos')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $proyectos->total() }} proyecto(s)</p>
        @can('create', App\Models\Proyecto::class)
            <a href="{{ route('proyectos.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Nuevo proyecto</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <x-form.input name="buscar" label="Nombre o código" :value="request('buscar')" />
        <x-form.select name="estado" label="Estado" :options="App\Models\Proyecto::ESTADOS" :value="request('estado')" placeholder="Todos" />
        <x-form.select name="prioridad" label="Prioridad" :options="App\Models\Proyecto::PRIORIDADES" :value="request('prioridad')" placeholder="Todas" />
        <x-form.select name="oficina_id" label="Oficina" :options="$oficinas" :value="request('oficina_id')" placeholder="Todas" />
        <x-form.select name="responsable_id" label="Responsable" :options="$policias" :value="request('responsable_id')" placeholder="Todos" />
        <div class="flex items-end gap-2 lg:col-span-5">
            <button class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">Filtrar</button>
            <a href="{{ route('proyectos.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Proyecto</th>
                    <th class="px-4 py-3">Responsable</th>
                    <th class="px-4 py-3">Fechas</th>
                    <th class="px-4 py-3">Integrantes</th>
                    <th class="px-4 py-3">Avance</th>
                    <th class="px-4 py-3">Prioridad</th>
                    <th class="px-4 py-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($proyectos as $proyecto)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('proyectos.show', $proyecto) }}" class="font-medium text-slate-900 hover:underline">{{ $proyecto->nombre }}</a>
                            <div class="text-xs text-slate-500">{{ $proyecto->codigo }} @if ($proyecto->oficina) · {{ $proyecto->oficina->nombre }} @endif</div>
                        </td>
                        <td class="px-4 py-3">{{ $proyecto->responsable?->nombre_completo ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-xs">
                            {{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }} → {{ $proyecto->fecha_fin?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">{{ $proyecto->integrantes_count }}</td>
                        <td class="px-4 py-3">@include('Proyecto._avance', ['valor' => $proyecto->porcentaje_avance])</td>
                        <td class="px-4 py-3"><x-badge :valor="$proyecto->prioridad" :texto="$proyecto->prioridad_etiqueta" /></td>
                        <td class="px-4 py-3"><x-badge :valor="$proyecto->estado" :texto="App\Models\Proyecto::ESTADOS[$proyecto->estado] ?? null" /></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay proyectos que coincidan con el filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $proyectos->links() }}</div>
@endsection
