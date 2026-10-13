@extends('layouts.app')

@section('titulo', 'Actividades')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $actividades->total() }} actividad(es)</p>
        @can('create', App\Models\Actividad::class)
            <a href="{{ route('actividades.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Nueva actividad</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6">
        <x-form.input name="buscar" label="Título" :value="request('buscar')" class="lg:col-span-2" />
        <x-form.select name="estado" label="Estado" :options="App\Models\Actividad::ESTADOS" :value="request('estado')" placeholder="Todos" />
        <x-form.select name="prioridad" label="Prioridad" :options="App\Models\Actividad::PRIORIDADES" :value="request('prioridad')" placeholder="Todas" />
        <x-form.select name="oficina_id" label="Oficina" :options="$oficinas" :value="request('oficina_id')" placeholder="Todas" />
        <x-form.input type="date" name="desde" label="Desde" :value="request('desde')" />
        <x-form.input type="date" name="hasta" label="Hasta" :value="request('hasta')" />
        <div class="flex items-end gap-2 lg:col-span-5">
            <button class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">Filtrar</button>
            <a href="{{ route('actividades.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Oficina</th>
                    <th class="px-4 py-3">Inicio</th>
                    <th class="px-4 py-3">Fin</th>
                    <th class="px-4 py-3">Prioridad</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($actividades as $actividad)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('actividades.show', $actividad) }}" class="font-medium text-slate-900 hover:underline">{{ $actividad->titulo }}</a>
                            @if ($actividad->tipo)
                                <div class="text-xs text-slate-500">{{ $actividad->tipo }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $actividad->oficina?->nombre }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $actividad->fecha_inicio->format('d/m/Y H:i') }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $actividad->fecha_fin?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3"><x-badge :valor="$actividad->prioridad" :texto="$actividad->prioridad_etiqueta" /></td>
                        <td class="px-4 py-3"><x-badge :valor="$actividad->estado" :texto="App\Models\Actividad::ESTADOS[$actividad->estado] ?? null" /></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            @can('update', $actividad)
                                <a href="{{ route('actividades.edit', $actividad) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Editar</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay actividades que coincidan con el filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $actividades->links() }}</div>
@endsection
