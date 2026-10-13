@extends('layouts.app')

@section('titulo', $actividad->titulo)

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('actividades.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Actividades</a>
        <div class="ml-auto flex items-center gap-4">
            @can('create', App\Models\Alerta::class)
                <a href="{{ route('alertas.create', ['alertable_tipo' => 'actividad', 'alertable_id' => $actividad->id]) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Crear alerta</a>
            @endcan
            @can('update', $actividad)
                <a href="{{ route('actividades.edit', $actividad) }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</a>
            @endcan
            @can('delete', $actividad)
                <x-boton-eliminar :accion="route('actividades.destroy', $actividad)" />
            @endcan
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap gap-2">
            <x-badge :valor="$actividad->estado" :texto="App\Models\Actividad::ESTADOS[$actividad->estado] ?? null" />
            <x-badge :valor="$actividad->prioridad" :texto="'Prioridad '.$actividad->prioridad_etiqueta" />
        </div>

        <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-slate-500">Oficina</dt><dd class="font-medium">{{ $actividad->oficina?->nombre }}</dd></div>
            <div><dt class="text-slate-500">Tipo</dt><dd class="font-medium">{{ $actividad->tipo ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Inicio</dt><dd class="font-medium">{{ $actividad->fecha_inicio->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-500">Fin</dt><dd class="font-medium">{{ $actividad->fecha_fin?->format('d/m/Y H:i') ?? '—' }}</dd></div>
        </dl>

        @if ($actividad->descripcion)
            <h2 class="mt-6 text-sm font-semibold text-slate-700">Descripción</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $actividad->descripcion }}</p>
        @endif

        @if ($actividad->observaciones)
            <h2 class="mt-6 text-sm font-semibold text-slate-700">Observaciones</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $actividad->observaciones }}</p>
        @endif
    </div>

    @include('Alerta._relacionadas', ['alertas' => $actividad->alertas])
@endsection
