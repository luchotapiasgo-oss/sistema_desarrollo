<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Inicio') · SIGDET</title>
    {{-- Con los assets compilados (npm run dev / build) se usa Vite; si no, Tailwind por CDN para desarrollo. --}}
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.1.11"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    @php
        $menu = [
            ['ruta' => 'actividades.index', 'texto' => 'Actividades', 'modelo' => App\Models\Actividad::class, 'prefijo' => 'actividades.*'],
            ['ruta' => 'proyectos.index', 'texto' => 'Proyectos', 'modelo' => App\Models\Proyecto::class, 'prefijo' => 'proyectos.*'],
            ['ruta' => 'incidencias.index', 'texto' => 'Incidencias', 'modelo' => App\Models\IncidenciaInfraestructura::class, 'prefijo' => 'incidencias.*'],
            ['ruta' => 'movimientos.index', 'texto' => 'Movimientos económicos', 'modelo' => App\Models\MovimientoEconomico::class, 'prefijo' => 'movimientos.*'],
            ['ruta' => 'alertas.index', 'texto' => 'Alertas', 'modelo' => null, 'prefijo' => 'alertas.*'],
            ['ruta' => 'auditorias.index', 'texto' => 'Auditoría', 'modelo' => App\Models\Auditoria::class, 'prefijo' => 'auditorias.*'],
        ];
        $alertasPendientes = auth()->check()
            ? App\Models\Alerta::deUsuario(auth()->id())->pendientes()->count()
            : 0;
    @endphp

    <div class="flex min-h-screen">
        <aside class="hidden w-60 shrink-0 bg-slate-900 text-slate-200 md:block">
            <div class="px-5 py-5 text-lg font-semibold tracking-wide text-white">SIGDET</div>
            <nav class="space-y-1 px-3">
                @foreach ($menu as $item)
                    @if (Route::has($item['ruta']) && ($item['modelo'] === null || auth()->user()?->can('viewAny', $item['modelo'])))
                        <a href="{{ route($item['ruta']) }}"
                           @class([
                               'flex items-center justify-between rounded-md px-3 py-2 text-sm transition',
                               'bg-slate-700 text-white' => request()->routeIs($item['prefijo']),
                               'hover:bg-slate-800' => ! request()->routeIs($item['prefijo']),
                           ])>
                            <span>{{ $item['texto'] }}</span>
                            @if ($item['ruta'] === 'alertas.index' && $alertasPendientes > 0)
                                <span class="rounded-full bg-red-500 px-2 text-xs font-semibold text-white">{{ $alertasPendientes }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3">
                <h1 class="text-lg font-semibold">@yield('titulo', 'Inicio')</h1>
                @auth
                    <div class="flex items-center gap-4 text-sm text-slate-600">
                        <span>{{ auth()->user()->nombre_usuario }}</span>
                        @if (Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="text-slate-500 hover:text-slate-900">Salir</button>
                            </form>
                        @endif
                    </div>
                @endauth
            </header>

            <main class="flex-1 p-4 md:p-6">
                @if (session('exito'))
                    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('exito') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-medium">Revise los datos ingresados:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('contenido')
            </main>
        </div>
    </div>
</body>
</html>
