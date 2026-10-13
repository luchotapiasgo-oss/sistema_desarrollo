@extends('layouts.app')

@section('titulo', $alerta->titulo)

@section('contenido')
    @php
        // Ficha del registro de origen, solo para los tipos con CRUD en este modulo.
        $rutasOrigen = ['actividad' => 'actividades.show', 'proyecto' => 'proyectos.show', 'incidencia' => 'incidencias.show'];
        $rutaOrigen = $rutasOrigen[$alerta->alertable_clave] ?? null;
    @endphp

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('alertas.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Alertas</a>
        <div class="ml-auto flex items-center gap-4">
            @can('atender', $alerta)
                <form method="POST" action="{{ route('alertas.atender', $alerta) }}">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-600">Marcar como atendida</button>
                </form>
            @endcan
            @can('update', $alerta)
                <a href="{{ route('alertas.edit', $alerta) }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</a>
            @endcan
            @can('delete', $alerta)
                <x-boton-eliminar :accion="route('alertas.destroy', $alerta)" />
            @endcan
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap gap-2">
            <x-badge :valor="$alerta->estado" :texto="App\Models\Alerta::ESTADOS[$alerta->estado] ?? null" />
            <x-badge :valor="$alerta->prioridad" :texto="'Prioridad '.$alerta->prioridad_etiqueta" />
            @if ($alerta->estaVencida())
                <x-badge valor="critica" texto="Vencida" />
            @endif
        </div>

        <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-slate-500">Tipo</dt><dd class="font-medium">{{ $alerta->tipo }}</dd></div>
            <div>
                <dt class="text-slate-500">Origen</dt>
                <dd class="font-medium">
                    @if ($rutaOrigen && $alerta->alertable && Route::has($rutaOrigen))
                        <a href="{{ route($rutaOrigen, $alerta->alertable) }}" class="hover:underline">{{ $alerta->alertable_etiqueta }}</a>
                    @else
                        {{ $alerta->alertable_etiqueta }}
                    @endif
                </dd>
            </div>
            <div><dt class="text-slate-500">Destinatario</dt><dd class="font-medium">{{ $alerta->usuario?->nombre_usuario ?? 'Sin destinatario' }}</dd></div>
            <div><dt class="text-slate-500">Generada</dt><dd class="font-medium">{{ $alerta->fecha_generacion->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-500">Vence</dt><dd class="font-medium">{{ $alerta->fecha_vencimiento?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Leída</dt><dd class="font-medium">{{ $alerta->fecha_lectura?->format('d/m/Y H:i') ?? '—' }}</dd></div>
        </dl>

        @if ($alerta->descripcion)
            <h2 class="mt-6 text-sm font-semibold text-slate-700">Descripción</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $alerta->descripcion }}</p>
        @endif
    </div>
@endsection
