{{-- Alertas asociadas a un registro (se incluye en las fichas de actividad, proyecto e incidencia). --}}
@if ($alertas->isNotEmpty())
    <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-3 text-base font-semibold">Alertas ({{ $alertas->count() }})</h2>
        <ul class="divide-y divide-slate-100 text-sm">
            @foreach ($alertas->sortByDesc('fecha_generacion') as $alerta)
                <li class="flex flex-wrap items-center gap-3 py-2">
                    @can('view', $alerta)
                        <a href="{{ route('alertas.show', $alerta) }}" class="font-medium hover:underline">{{ $alerta->titulo }}</a>
                    @else
                        <span class="font-medium">{{ $alerta->titulo }}</span>
                    @endcan
                    <span class="text-xs text-slate-500">{{ $alerta->fecha_generacion->format('d/m/Y') }}</span>
                    <span class="ml-auto flex gap-2">
                        <x-badge :valor="$alerta->prioridad" :texto="$alerta->prioridad_etiqueta" />
                        <x-badge :valor="$alerta->estado" :texto="App\Models\Alerta::ESTADOS[$alerta->estado] ?? null" />
                    </span>
                </li>
            @endforeach
        </ul>
    </section>
@endif
