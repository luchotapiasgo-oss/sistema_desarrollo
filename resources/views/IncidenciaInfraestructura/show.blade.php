@extends('layouts.app')

@section('titulo', $incidencia->titulo)

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('incidencias.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Incidencias</a>
        <div class="ml-auto flex items-center gap-4">
            @can('create', App\Models\Alerta::class)
                <a href="{{ route('alertas.create', ['alertable_tipo' => 'incidencia', 'alertable_id' => $incidencia->id]) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Crear alerta</a>
            @endcan
            @can('update', $incidencia)
                <a href="{{ route('incidencias.edit', $incidencia) }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</a>
            @endcan
            @can('delete', $incidencia)
                <x-boton-eliminar :accion="route('incidencias.destroy', $incidencia)" />
            @endcan
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap gap-2">
            <x-badge :valor="$incidencia->estado" :texto="App\Models\IncidenciaInfraestructura::ESTADOS[$incidencia->estado] ?? null" />
            <x-badge :valor="$incidencia->prioridad" :texto="'Prioridad '.$incidencia->prioridad_etiqueta" />
            <x-badge valor="tipo" :texto="App\Models\IncidenciaInfraestructura::TIPOS[$incidencia->tipo] ?? $incidencia->tipo" />
        </div>

        <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-slate-500">Oficina</dt><dd class="font-medium">{{ $incidencia->oficina?->nombre ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Responsable</dt><dd class="font-medium">{{ $incidencia->responsable?->nombre_completo ?? 'Sin asignar' }}</dd></div>
            <div><dt class="text-slate-500">Costo</dt><dd class="font-medium">{{ $incidencia->costo !== null ? '$ '.number_format((float) $incidencia->costo, 2, ',', '.') : '—' }}</dd></div>
            <div><dt class="text-slate-500">Reportada</dt><dd class="font-medium">{{ $incidencia->fecha_reporte->format('d/m/Y H:i') }}</dd></div>
            <div>
                <dt class="text-slate-500">Resuelta</dt>
                <dd class="font-medium">
                    @if ($incidencia->fecha_resolucion)
                        {{ $incidencia->fecha_resolucion->format('d/m/Y H:i') }}
                        <span class="text-xs text-slate-500">({{ $incidencia->fecha_reporte->diffForHumans($incidencia->fecha_resolucion, true) }})</span>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>

        @if ($incidencia->descripcion)
            <h2 class="mt-6 text-sm font-semibold text-slate-700">Descripción</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $incidencia->descripcion }}</p>
        @endif

        @if ($incidencia->observaciones)
            <h2 class="mt-6 text-sm font-semibold text-slate-700">Observaciones</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $incidencia->observaciones }}</p>
        @endif
    </div>

    @can('resolver', $incidencia)
        <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold">Resolver incidencia</h2>
            <form method="POST" action="{{ route('incidencias.resolver', $incidencia) }}" class="grid gap-4 md:grid-cols-3">
                @csrf
                @method('PATCH')
                <x-form.input type="datetime-local" name="fecha_resolucion" label="Fecha de resolución" :value="now()->format('Y-m-d\TH:i')" required />
                <x-form.input type="number" name="costo" label="Costo final ($)" :value="$incidencia->costo" min="0" step="0.01" />
                <div class="hidden md:block"></div>
                <x-form.textarea name="observaciones" label="Observaciones de cierre" class="md:col-span-3" rows="2" />
                <div>
                    <button class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-600">Marcar como resuelta</button>
                </div>
            </form>
        </section>
    @endcan

    @include('Alerta._relacionadas', ['alertas' => $incidencia->alertas])
@endsection
