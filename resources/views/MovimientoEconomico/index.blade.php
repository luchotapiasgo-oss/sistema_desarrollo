@extends('layouts.app')

@section('titulo', 'Movimientos económicos')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-end gap-3">
        @can('export', App\Models\MovimientoEconomico::class)
            <a href="{{ route('movimientos.exportar', request()->query()) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Exportar CSV</a>
        @endcan
        @can('create', App\Models\MovimientoEconomico::class)
            <a href="{{ route('movimientos.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Nuevo movimiento</a>
        @endcan
    </div>

    <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-slate-500">Ingresos</p><p class="text-xl font-semibold text-green-700">$ {{ number_format($ingresos, 2, ',', '.') }}</p></div>
        <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-slate-500">Egresos</p><p class="text-xl font-semibold text-red-700">$ {{ number_format($egresos, 2, ',', '.') }}</p></div>
        <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-slate-500">Balance</p><p @class(['text-xl font-semibold', 'text-red-700' => $ingresos - $egresos < 0])>$ {{ number_format($ingresos - $egresos, 2, ',', '.') }}</p></div>
    </div>
    <p class="-mt-2 mb-4 text-xs text-slate-500">Totales sobre todos los movimientos filtrados, sin contar los anulados.</p>

    <form method="GET" class="mb-4 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-7">
        <x-form.input name="buscar" label="Concepto, proveedor o comprobante" :value="request('buscar')" class="lg:col-span-2" />
        <x-form.select name="tipo" label="Tipo" :options="App\Models\MovimientoEconomico::TIPOS" :value="request('tipo')" placeholder="Todos" />
        <x-form.select name="estado" label="Estado" :options="App\Models\MovimientoEconomico::ESTADOS" :value="request('estado')" placeholder="Todos" />
        <x-form.select name="proyecto_id" label="Proyecto" :options="$proyectos" :value="request('proyecto_id')" placeholder="Todos" />
        <x-form.input type="date" name="desde" label="Desde" :value="request('desde')" />
        <x-form.input type="date" name="hasta" label="Hasta" :value="request('hasta')" />
        <div class="flex items-end gap-2 lg:col-span-7">
            <button class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">Filtrar</button>
            <a href="{{ route('movimientos.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Concepto</th>
                    <th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3">Proyecto</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Monto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($movimientos as $movimiento)
                    <tr @class(['hover:bg-slate-50', 'text-slate-400' => $movimiento->estado === 'anulado'])>
                        <td class="whitespace-nowrap px-4 py-3">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('movimientos.show', $movimiento) }}" class="font-medium hover:underline">{{ $movimiento->concepto }}</a>
                            @if ($movimiento->proveedor)
                                <div class="text-xs text-slate-500">{{ $movimiento->proveedor }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $movimiento->categoria }}</td>
                        <td class="px-4 py-3">{{ $movimiento->proyecto?->codigo ?? '—' }}</td>
                        <td class="px-4 py-3"><x-badge :valor="$movimiento->tipo" :texto="App\Models\MovimientoEconomico::TIPOS[$movimiento->tipo] ?? null" /></td>
                        <td class="px-4 py-3"><x-badge :valor="$movimiento->estado" :texto="App\Models\MovimientoEconomico::ESTADOS[$movimiento->estado] ?? null" /></td>
                        <td @class(['whitespace-nowrap px-4 py-3 text-right font-medium tabular-nums', 'text-red-700' => $movimiento->tipo === 'egreso' && $movimiento->estado !== 'anulado'])>
                            {{ $movimiento->tipo === 'egreso' ? '−' : '' }}$ {{ number_format((float) $movimiento->monto, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay movimientos que coincidan con el filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $movimientos->links() }}</div>
@endsection
