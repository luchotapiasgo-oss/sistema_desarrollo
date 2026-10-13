{{-- Etiqueta de estado o prioridad. El color sale del valor crudo de la columna. --}}
@props(['valor', 'texto' => null])

@php
    $colores = [
        'pendiente' => 'bg-amber-100 text-amber-800',
        'planificado' => 'bg-slate-200 text-slate-700',
        'registrado' => 'bg-slate-200 text-slate-700',
        'en_curso' => 'bg-blue-100 text-blue-800',
        'en_proceso' => 'bg-blue-100 text-blue-800',
        'leida' => 'bg-sky-100 text-sky-800',
        'suspendido' => 'bg-orange-100 text-orange-800',
        'finalizada' => 'bg-green-100 text-green-800',
        'finalizado' => 'bg-green-100 text-green-800',
        'resuelta' => 'bg-green-100 text-green-800',
        'atendida' => 'bg-green-100 text-green-800',
        'aprobado' => 'bg-green-100 text-green-800',
        'cancelada' => 'bg-slate-100 text-slate-500 line-through',
        'cancelado' => 'bg-slate-100 text-slate-500 line-through',
        'anulado' => 'bg-slate-100 text-slate-500 line-through',
        'ingreso' => 'bg-green-100 text-green-800',
        'egreso' => 'bg-red-100 text-red-800',
        'baja' => 'bg-slate-100 text-slate-600',
        'media' => 'bg-blue-50 text-blue-700',
        'alta' => 'bg-amber-100 text-amber-800',
        'critica' => 'bg-red-100 text-red-800',
        'crear' => 'bg-green-100 text-green-800',
        'editar' => 'bg-blue-100 text-blue-800',
        'eliminar' => 'bg-red-100 text-red-800',
        'restaurar' => 'bg-amber-100 text-amber-800',
    ];
@endphp

<span {{ $attributes->class(['inline-block whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium', $colores[$valor] ?? 'bg-slate-100 text-slate-700']) }}>
    {{ $texto ?? \Illuminate\Support\Str::headline((string) $valor) }}
</span>
