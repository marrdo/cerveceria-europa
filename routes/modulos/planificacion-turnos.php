<?php

use App\Modulos\PlanificacionTurnos\Http\Controllers\Admin\CuadranteLaboralController;
use App\Modulos\PlanificacionTurnos\Http\Controllers\Empleado\MisTurnosController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/mis-turnos', MisTurnosController::class)
    ->middleware(['auth', 'modulo.publico:planificacion_turnos'])
    ->name('admin.mis-turnos.index');

Route::middleware(['auth', 'modulo:planificacion_turnos'])
    ->prefix('admin/planificacion-turnos')
    ->name('admin.planificacion-turnos.')
    ->group(function (): void {
        Route::get('/', [CuadranteLaboralController::class, 'index'])->name('cuadrantes.index');
        Route::post('/', [CuadranteLaboralController::class, 'store'])->name('cuadrantes.store');
        Route::get('cuadrantes/{cuadrante}', [CuadranteLaboralController::class, 'show'])->name('cuadrantes.show');
        Route::post('cuadrantes/{cuadrante}/jornadas', [CuadranteLaboralController::class, 'storeJornada'])->name('cuadrantes.jornadas.store');
        Route::post('cuadrantes/{cuadrante}/jornadas/bloque', [CuadranteLaboralController::class, 'storeJornadasBloque'])->name('cuadrantes.jornadas.bloque');
        Route::delete('cuadrantes/{cuadrante}/jornadas/{jornada}', [CuadranteLaboralController::class, 'destroyJornada'])->name('cuadrantes.jornadas.destroy');
        Route::post('cuadrantes/{cuadrante}/incidencias', [CuadranteLaboralController::class, 'storeIncidencia'])->name('cuadrantes.incidencias.store');
        Route::delete('cuadrantes/{cuadrante}/incidencias/{incidencia}', [CuadranteLaboralController::class, 'destroyIncidencia'])->name('cuadrantes.incidencias.destroy');
        Route::post('cuadrantes/{cuadrante}/copiar', [CuadranteLaboralController::class, 'copiar'])->name('cuadrantes.copiar');
        Route::post('cuadrantes/{cuadrante}/plantillas', [CuadranteLaboralController::class, 'storePlantilla'])->name('cuadrantes.plantillas.store');
        Route::post('plantillas/{plantilla}/aplicar', [CuadranteLaboralController::class, 'aplicarPlantilla'])->name('plantillas.aplicar');
        Route::delete('plantillas/{plantilla}', [CuadranteLaboralController::class, 'destroyPlantilla'])->name('plantillas.destroy');
        Route::post('coberturas', [CuadranteLaboralController::class, 'storeCobertura'])->name('coberturas.store');
        Route::delete('coberturas/{cobertura}', [CuadranteLaboralController::class, 'destroyCobertura'])->name('coberturas.destroy');
        Route::patch('cuadrantes/{cuadrante}/publicar', [CuadranteLaboralController::class, 'publicar'])->name('cuadrantes.publicar');
        Route::patch('cuadrantes/{cuadrante}/reabrir', [CuadranteLaboralController::class, 'reabrir'])->name('cuadrantes.reabrir');
        Route::get('cuadrantes/{cuadrante}/exportaciones/{exportacion}/descargar', [CuadranteLaboralController::class, 'descargarExportacion'])->name('cuadrantes.exportaciones.descargar');
    });
