@extends('layouts.app')

@section('titulo', $proyecto->nombre)

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('proyectos.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Proyectos</a>
        <div class="ml-auto flex items-center gap-4">
            @can('create', App\Models\Alerta::class)
                <a href="{{ route('alertas.create', ['alertable_tipo' => 'proyecto', 'alertable_id' => $proyecto->id]) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Crear alerta</a>
            @endcan
            @can('update', $proyecto)
                <a href="{{ route('proyectos.edit', $proyecto) }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</a>
            @endcan
            @can('delete', $proyecto)
                <x-boton-eliminar :accion="route('proyectos.destroy', $proyecto)"
                                  mensaje="Se eliminará el proyecto y sus integrantes. Los movimientos económicos quedarán sin proyecto. ¿Continuar?" />
            @endcan
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="font-mono text-sm text-slate-500">{{ $proyecto->codigo }}</span>
            <x-badge :valor="$proyecto->estado" :texto="App\Models\Proyecto::ESTADOS[$proyecto->estado] ?? null" />
            <x-badge :valor="$proyecto->prioridad" :texto="'Prioridad '.$proyecto->prioridad_etiqueta" />
        </div>

        <div class="mb-6 max-w-md">
            <p class="mb-1 text-sm text-slate-500">Avance</p>
            @include('Proyecto._avance', ['valor' => $proyecto->porcentaje_avance])
        </div>

        <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-slate-500">Oficina</dt><dd class="font-medium">{{ $proyecto->oficina?->nombre ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Responsable</dt><dd class="font-medium">{{ $proyecto->responsable?->nombre_completo ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Inicio</dt><dd class="font-medium">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}</dd></div>
            <div>
                <dt class="text-slate-500">Fin</dt>
                <dd class="font-medium">
                    {{ $proyecto->fecha_fin?->format('d/m/Y') ?? '—' }}
                    @if ($proyecto->fecha_fin && $proyecto->fecha_fin->isPast() && ! in_array($proyecto->estado, ['finalizado', 'cancelado']))
                        <span class="ml-1 text-xs font-semibold text-red-600">vencido</span>
                    @endif
                </dd>
            </div>
        </dl>

        @foreach (['descripcion' => 'Descripción', 'objetivo' => 'Objetivo', 'observaciones' => 'Observaciones'] as $campo => $titulo)
            @if ($proyecto->{$campo})
                <h2 class="mt-6 text-sm font-semibold text-slate-700">{{ $titulo }}</h2>
                <p class="mt-1 whitespace-pre-line text-sm">{{ $proyecto->{$campo} }}</p>
            @endif
        @endforeach
    </div>

    {{-- Integrantes --}}
    <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-base font-semibold">Integrantes ({{ $proyecto->integrantes->count() }})</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Policía</th>
                        <th class="py-2 pr-4">Ingreso</th>
                        <th class="py-2 pr-4">Salida</th>
                        <th class="py-2 pr-4">Observaciones</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($proyecto->integrantes as $integrante)
                        <tr>
                            <td class="py-2 pr-4 font-medium">
                                {{ $integrante->policia?->nombre_completo }}
                                @if ($integrante->policia_id === $proyecto->responsable_id)
                                    <span class="ml-1 text-xs text-slate-500">(responsable)</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4">{{ $integrante->fecha_ingreso?->format('d/m/Y') ?? '—' }}</td>
                            <td class="py-2 pr-4">{{ $integrante->fecha_salida?->format('d/m/Y') ?? '—' }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ \Illuminate\Support\Str::limit($integrante->observaciones, 60) }}</td>
                            <td class="whitespace-nowrap py-2 text-right">
                                @can('gestionarIntegrantes', $proyecto)
                                    <a href="{{ route('proyectos.integrantes.edit', [$proyecto, $integrante]) }}" class="mr-3 text-sm font-medium text-slate-600 hover:text-slate-900">Editar</a>
                                    <x-boton-eliminar :accion="route('proyectos.integrantes.destroy', [$proyecto, $integrante])" texto="Quitar" mensaje="¿Quitar a este integrante del proyecto?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-slate-500">El proyecto todavía no tiene integrantes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @can('gestionarIntegrantes', $proyecto)
            <form method="POST" action="{{ route('proyectos.integrantes.store', $proyecto) }}" class="mt-6 grid gap-3 border-t border-slate-100 pt-4 md:grid-cols-4">
                @csrf
                <x-form.select name="policia_id" label="Agregar policía" :options="$policiasDisponibles" required class="md:col-span-2" />
                <x-form.input type="date" name="fecha_ingreso" label="Fecha de ingreso" :value="today()->format('Y-m-d')" />
                <x-form.input type="date" name="fecha_salida" label="Fecha de salida" />
                <x-form.input name="observaciones" label="Observaciones" class="md:col-span-3" />
                <div class="flex items-end">
                    <button class="w-full rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Agregar</button>
                </div>
            </form>
        @endcan
    </section>

    {{-- Movimientos economicos --}}
    @can('viewAny', App\Models\MovimientoEconomico::class)
        <section class="mt-6 rounded-lg bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold">Movimientos económicos</h2>
                @can('create', App\Models\MovimientoEconomico::class)
                    <a href="{{ route('movimientos.create', ['proyecto_id' => $proyecto->id]) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Registrar movimiento</a>
                @endcan
            </div>

            <div class="mb-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-md bg-green-50 p-3"><p class="text-xs text-green-700">Ingresos</p><p class="text-lg font-semibold text-green-800">$ {{ number_format($ingresos, 2, ',', '.') }}</p></div>
                <div class="rounded-md bg-red-50 p-3"><p class="text-xs text-red-700">Egresos</p><p class="text-lg font-semibold text-red-800">$ {{ number_format($egresos, 2, ',', '.') }}</p></div>
                <div class="rounded-md bg-slate-50 p-3"><p class="text-xs text-slate-600">Balance</p><p @class(['text-lg font-semibold', 'text-red-700' => $ingresos - $egresos < 0])>$ {{ number_format($ingresos - $egresos, 2, ',', '.') }}</p></div>
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <tbody class="divide-y divide-slate-100">
                    @forelse ($proyecto->movimientos->take(10) as $movimiento)
                        <tr>
                            <td class="py-2 pr-4 whitespace-nowrap">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                            <td class="py-2 pr-4"><a href="{{ route('movimientos.show', $movimiento) }}" class="hover:underline">{{ $movimiento->concepto }}</a></td>
                            <td class="py-2 pr-4"><x-badge :valor="$movimiento->tipo" /></td>
                            <td class="py-2 pr-4"><x-badge :valor="$movimiento->estado" /></td>
                            <td class="py-2 text-right tabular-nums">$ {{ number_format((float) $movimiento->monto, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-4 text-center text-slate-500">Sin movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($proyecto->movimientos->count() > 10)
                <a href="{{ route('movimientos.index', ['proyecto_id' => $proyecto->id]) }}" class="mt-3 inline-block text-sm text-slate-600 hover:underline">Ver los {{ $proyecto->movimientos->count() }} movimientos</a>
            @endif
        </section>
    @endcan

    @include('Alerta._relacionadas', ['alertas' => $proyecto->alertas])
@endsection
