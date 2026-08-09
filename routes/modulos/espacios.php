<?php

use App\Modulos\Espacios\Http\Controllers\Admin\MesaController;
use App\Modulos\Espacios\Http\Controllers\Admin\RecintoController;
use App\Modulos\Espacios\Http\Controllers\Admin\ZonaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'modulo:espacios'])
    ->prefix('admin/espacios')
    ->name('admin.espacios.')
    ->group(function (): void {
        Route::get('/', fn () => redirect()->route('admin.espacios.recintos.index'))->name('index');
        Route::resource('recintos', RecintoController::class)->except(['show']);
        Route::resource('zonas', ZonaController::class)->except(['show']);
        Route::resource('mesas', MesaController::class)->except(['show']);
    });
