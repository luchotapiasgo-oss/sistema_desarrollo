<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\IncidenciaInfraestructuraController;
use App\Http\Controllers\MovimientoEconomicoController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ProyectoIntegranteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Actividades, proyectos e infraestructura.
    Route::resource('actividades', ActividadController::class)
        ->parameters(['actividades' => 'actividad']);

    Route::resource('proyectos', ProyectoController::class);
    Route::resource('proyectos.integrantes', ProyectoIntegranteController::class)
        ->only(['store', 'edit', 'update', 'destroy'])
        ->parameters(['integrantes' => 'integrante'])
        ->scoped();

    Route::patch('incidencias/{incidencia}/resolver', [IncidenciaInfraestructuraController::class, 'resolver'])
        ->name('incidencias.resolver');
    Route::resource('incidencias', IncidenciaInfraestructuraController::class)
        ->parameters(['incidencias' => 'incidencia']);

    Route::get('movimientos/exportar', [MovimientoEconomicoController::class, 'exportar'])
        ->name('movimientos.exportar');
    Route::resource('movimientos', MovimientoEconomicoController::class)
        ->parameters(['movimientos' => 'movimiento']);

    Route::get('auditorias/exportar', [AuditoriaController::class, 'exportar'])
        ->name('auditorias.exportar');
    Route::resource('auditorias', AuditoriaController::class)
        ->only(['index', 'show']);

    Route::patch('alertas/{alerta}/atender', [AlertaController::class, 'atender'])
        ->name('alertas.atender');
    Route::resource('alertas', AlertaController::class);
});
