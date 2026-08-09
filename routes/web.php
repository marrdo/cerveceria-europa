<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModuloController;
use App\Http\Controllers\ProfileController;
use App\Modulos\Configuracion\Http\Controllers\Admin\ConfiguracionNegocioController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/admin', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::patch('/admin/modulos/{modulo}/toggle', [ModuloController::class, 'toggle'])
        ->name('admin.modulos.toggle');

    Route::get('/admin/configuracion/negocio', [ConfiguracionNegocioController::class, 'edit'])
        ->name('admin.configuracion.negocio.edit');
    Route::put('/admin/configuracion/negocio', [ConfiguracionNegocioController::class, 'update'])
        ->name('admin.configuracion.negocio.update');
});

require __DIR__.'/modulos/personal.php';
require __DIR__.'/modulos/planificacion-turnos.php';
require __DIR__.'/modulos/inventario.php';
require __DIR__.'/modulos/compras.php';
require __DIR__.'/modulos/ventas.php';
require __DIR__.'/modulos/espacios.php';
require __DIR__.'/modulos/web-publica.php';

require __DIR__.'/auth.php';
