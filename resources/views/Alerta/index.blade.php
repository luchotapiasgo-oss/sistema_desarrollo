@extends('layouts.app')

@section('titulo', 'Alertas')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            {{ $alertas->total() }} alerta(s)
            @unless ($verTodas) · solo se muestran las dirigidas a usted @endunless
        </p>
        @can('create', App\Models\Alerta::class)
            <a href="{{ route('alertas.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Nueva alerta</a>
        @endcan
    </div>

    @php($tiposAlertable = collect(App\Models\Alerta::ALERTABLES)->keys()->mapWithKeys(fn ($clave) => [$clave => \Illuminate\Support\Str::headline($clave)]))

    <form method="GET" class="mb-4 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <x-form.select name="estado" label="Estado" :options="App\Models\Alerta::ESTADOS" :value="request('estado')" placeholder="Todos" />
        <x-form.select name="prioridad" label="Prioridad" :options="App\Models\Alerta::PRIORIDADES" :value="request('prioridad')" placeholder="Todas" />
        <x-form.select name="alertable_tipo" label="Origen" :options="$tiposAlertable" :value="request('alertable_tipo')" placeholder="Todos" />
        <div class="flex items-end gap-4 pb-2 text-sm">
            @if ($verTodas)
                <label class="flex items-center gap-2"><input type="checkbox" name="mias" value="1" @checked(request()->boolean('mias'))> Solo mías</label>
            @endif
            <label class="flex items-center gap-2"><input type="checkbox" name="vencidas" value="1" @checked(request()->boolean('vencidas'))> Vencidas</label>
        </div>
        <div class="flex items-end gap-2">
            <button class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">Filtrar</button>
            <a href="{{ route('alertas.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Alerta</th>
                    <th class="px-4 py-3">Origen</th>
                    <th class="px-4 py-3">Destinatario</th>
                    <th class="px-4 py-3">Vence</th>
                    <th class="px-4 py-3">Prioridad</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($alertas as $alerta)
                    <tr @class(['hover:bg-slate-50', 'font-semibold' => $alerta->estado === 'pendiente'])>
                        <td class="px-4 py-3">
                            <a href="{{ route('alertas.show', $alerta) }}" class="text-slate-900 hover:underline">{{ $alerta->titulo }}</a>
                            <div class="text-xs font-normal text-slate-500">{{ $alerta->tipo }} · {{ $alerta->fecha_generacion->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-4 py-3 font-normal">{{ $alerta->alertable_etiqueta }}</td>
                        <td class="px-4 py-3 font-normal">{{ $alerta->usuario?->nombre_usuario ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 font-normal">
                            {{ $alerta->fecha_vencimiento?->format('d/m/Y H:i') ?? '—' }}
                            @if ($alerta->estaVencida())
                                <span class="ml-1 text-xs font-semibold text-red-600">vencida</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"><x-badge :valor="$alerta->prioridad" :texto="$alerta->prioridad_etiqueta" /></td>
                        <td class="px-4 py-3"><x-badge :valor="$alerta->estado" :texto="App\Models\Alerta::ESTADOS[$alerta->estado] ?? null" /></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            @can('atender', $alerta)
                                <form method="POST" action="{{ route('alertas.atender', $alerta) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-sm font-medium text-green-700 hover:text-green-900">Atender</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay alertas que coincidan con el filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $alertas->links() }}</div>
@endsection
