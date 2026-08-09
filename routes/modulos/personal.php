<?php

use App\Http\Controllers\Admin\PersonalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'modulo:personal'])
    ->prefix('admin/personal')
    ->name('admin.personal.')
    ->group(function (): void {
        Route::get('/', [PersonalController::class, 'index'])->name('index');
        Route::get('usuarios/create', [PersonalController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [PersonalController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}', [PersonalController::class, 'show'])->name('usuarios.show');
        Route::get('usuarios/{usuario}/edit', [PersonalController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [PersonalController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [PersonalController::class, 'destroy'])->name('usuarios.destroy');
    });
