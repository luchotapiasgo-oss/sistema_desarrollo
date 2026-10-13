@extends('layouts.app')

@section('titulo', 'Incidencias de infraestructura')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            {{ $incidencias->total() }} incidencia(s) · costo total
            <span class="font-semibold text-slate-700">$ {{ number_format($costoTotal, 2, ',', '.') }}</span>
        </p>
        @can('create', App\Models\IncidenciaInfraestructura::class)
            <a href="{{ route('incidencias.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Reportar incidencia</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-7">
        <x-form.input name="buscar" label="Título" :value="request('buscar')" />
        <x-form.select name="tipo" label="Tipo" :options="App\Models\IncidenciaInfraestructura::TIPOS" :value="request('tipo')" placeholder="Todos" />
        <x-form.select name="estado" label="Estado" :options="App\Models\IncidenciaInfraestructura::ESTADOS" :value="request('estado')" placeholder="Todos" />
        <x-form.select name="prioridad" label="Prioridad" :options="App\Models\IncidenciaInfraestructura::PRIORIDADES" :value="request('prioridad')" placeholder="Todas" />
        <x-form.select name="oficina_id" label="Oficina" :options="$oficinas" :value="request('oficina_id')" placeholder="Todas" />
        <x-form.input type="date" name="desde" label="Reportada desde" :value="request('desde')" />
        <x-form.input type="date" name="hasta" label="Hasta" :value="request('hasta')" />
        <div class="flex items-end gap-2 lg:col-span-7">
            <button class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">Filtrar</button>
            <a href="{{ route('incidencias.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Incidencia</th>
                    <th class="px-4 py-3">Oficina</th>
                    <th class="px-4 py-3">Responsable</th>
                    <th class="px-4 py-3">Reporte</th>
                    <th class="px-4 py-3 text-right">Costo</th>
                    <th class="px-4 py-3">Prioridad</th>
                    <th class="px-4 py-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($incidencias as $incidencia)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('incidencias.show', $incidencia) }}" class="font-medium text-slate-900 hover:underline">{{ $incidencia->titulo }}</a>
                            <div class="text-xs text-slate-500">{{ App\Models\IncidenciaInfraestructura::TIPOS[$incidencia->tipo] ?? $incidencia->tipo }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $incidencia->oficina?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $incidencia->responsable?->nombre_completo ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $incidencia->fecha_reporte->format('d/m/Y H:i') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ $incidencia->costo !== null ? '$ '.number_format((float) $incidencia->costo, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-3"><x-badge :valor="$incidencia->prioridad" :texto="$incidencia->prioridad_etiqueta" /></td>
                        <td class="px-4 py-3"><x-badge :valor="$incidencia->estado" :texto="App\Models\IncidenciaInfraestructura::ESTADOS[$incidencia->estado] ?? null" /></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay incidencias que coincidan con el filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $incidencias->links() }}</div>
@endsection
