@php($porcentaje = max(0, min(100, (float) $valor)))
<div class="flex min-w-28 items-center gap-2">
    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
        <div @class(['h-full rounded-full', 'bg-green-500' => $porcentaje >= 100, 'bg-blue-500' => $porcentaje < 100]) style="width: {{ $porcentaje }}%"></div>
    </div>
    <span class="w-12 text-right text-xs tabular-nums text-slate-600">{{ number_format($porcentaje, 0) }}%</span>
</div>
