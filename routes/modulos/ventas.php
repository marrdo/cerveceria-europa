<?php

use App\Modulos\Ventas\Http\Controllers\Admin\ComandaController;
use App\Modulos\Ventas\Http\Controllers\Admin\InformeVentasController;
use App\Modulos\Ventas\Http\Controllers\Admin\TurnoCajaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'modulo:ventas'])
    ->prefix('admin/ventas')
    ->name('admin.ventas.')
    ->group(function (): void {
        Route::get('/', [ComandaController::class, 'index'])->name('index');
        Route::get('caja', [TurnoCajaController::class, 'index'])->name('caja.index');
        Route::post('caja', [TurnoCajaController::class, 'store'])->name('caja.store');
        Route::get('caja/{caja}', [TurnoCajaController::class, 'show'])->name('caja.show');
        Route::patch('caja/{caja}/cerrar', [TurnoCajaController::class, 'cerrar'])->name('caja.cerrar');
        Route::get('informes', [InformeVentasController::class, 'index'])->name('informes.index');
        Route::patch('comandas/{comanda}/servir', [ComandaController::class, 'servir'])->name('comandas.servir');
        Route::patch('comandas/{comanda}/cancelar', [ComandaController::class, 'cancelar'])->name('comandas.cancelar');
        Route::patch('comandas/{comanda}/operativa', [ComandaController::class, 'actualizarOperativa'])->name('comandas.operativa.update');
        Route::post('comandas/{comanda}/lineas', [ComandaController::class, 'agregarLineas'])->name('comandas.lineas.store');
        Route::post('comandas/{comanda}/pagos', [ComandaController::class, 'cobrar'])->name('comandas.pagos.store');
        Route::patch('comandas/{comanda}/lineas/{linea}/servir', [ComandaController::class, 'servirLinea'])->name('comandas.lineas.servir');
        Route::resource('comandas', ComandaController::class)->only(['index', 'create', 'store', 'show']);
    });
