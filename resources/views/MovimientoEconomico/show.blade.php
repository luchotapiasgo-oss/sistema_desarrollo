@extends('layouts.app')

@section('titulo', $movimiento->concepto)

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('movimientos.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Movimientos</a>
        <div class="ml-auto flex items-center gap-4">
            @can('update', $movimiento)
                <a href="{{ route('movimientos.edit', $movimiento) }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</a>
            @endcan
            @can('delete', $movimiento)
                <x-boton-eliminar :accion="route('movimientos.destroy', $movimiento)" />
            @endcan
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <x-badge :valor="$movimiento->tipo" :texto="App\Models\MovimientoEconomico::TIPOS[$movimiento->tipo] ?? null" />
            <x-badge :valor="$movimiento->estado" :texto="App\Models\MovimientoEconomico::ESTADOS[$movimiento->estado] ?? null" />
            <span @class(['text-2xl font-semibold tabular-nums', 'text-red-700' => $movimiento->tipo === 'egreso', 'text-green-700' => $movimiento->tipo === 'ingreso'])>
                $ {{ number_format((float) $movimiento->monto, 2, ',', '.') }}
            </span>
        </div>

        <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-slate-500">Fecha</dt><dd class="font-medium">{{ $movimiento->fecha->format('d/m/Y') }}</dd></div>
            <div><dt class="text-slate-500">Categoría</dt><dd class="font-medium">{{ $movimiento->categoria }}</dd></div>
            <div><dt class="text-slate-500">Proveedor</dt><dd class="font-medium">{{ $movimiento->proveedor ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Comprobante</dt><dd class="font-medium">{{ $movimiento->comprobante ?? '—' }}</dd></div>
            <div>
                <dt class="text-slate-500">Proyecto</dt>
                <dd class="font-medium">
                    @if ($movimiento->proyecto)
                        <a href="{{ route('proyectos.show', $movimiento->proyecto) }}" class="hover:underline">{{ $movimiento->proyecto->codigo }} - {{ $movimiento->proyecto->nombre }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div><dt class="text-slate-500">Responsable</dt><dd class="font-medium">{{ $movimiento->responsable?->nombre_completo ?? '—' }}</dd></div>
        </dl>

        @if ($movimiento->descripcion)
            <h2 class="mt-6 text-sm font-semibold text-slate-700">Descripción</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $movimiento->descripcion }}</p>
        @endif

        @if ($movimiento->observaciones)
            <h2 class="mt-6 text-sm font-semibold text-slate-700">Observaciones</h2>
            <p class="mt-1 whitespace-pre-line text-sm">{{ $movimiento->observaciones }}</p>
        @endif

        <p class="mt-6 text-xs text-slate-400">Registrado el {{ $movimiento->created_at?->format('d/m/Y H:i') }} · última modificación {{ $movimiento->updated_at?->format('d/m/Y H:i') }}</p>
    </div>
@endsection
