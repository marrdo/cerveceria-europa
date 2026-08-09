<?php

use App\Modulos\Inventario\Http\Controllers\Admin\CategoriaProductoController;
use App\Modulos\Inventario\Http\Controllers\Admin\DashboardInventarioController;
use App\Modulos\Inventario\Http\Controllers\Admin\InformeInventarioController;
use App\Modulos\Inventario\Http\Controllers\Admin\ProductoController;
use App\Modulos\Inventario\Http\Controllers\Admin\ProveedorController;
use App\Modulos\Inventario\Http\Controllers\Admin\UbicacionInventarioController;
use App\Modulos\Inventario\Http\Controllers\Admin\UnidadInventarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'modulo:inventario'])
    ->prefix('admin/inventario')
    ->name('admin.inventario.')
    ->group(function (): void {
        Route::get('/', DashboardInventarioController::class)->name('index');
        Route::get('alertas', [InformeInventarioController::class, 'alertas'])->name('alertas.index');
        Route::get('alertas/exportar', [InformeInventarioController::class, 'exportarAlertas'])->name('alertas.exportar');
        Route::get('descuadres/exportar', [InformeInventarioController::class, 'exportarDescuadres'])->name('descuadres.exportar');
        Route::get('movimientos', [InformeInventarioController::class, 'movimientos'])->name('movimientos.index');
        Route::get('movimientos/exportar', [InformeInventarioController::class, 'exportarMovimientos'])->name('movimientos.exportar');
        Route::get('productos/exportar', [InformeInventarioController::class, 'exportarProductos'])->name('productos.exportar');

        Route::resource('productos', ProductoController::class)
            ->except(['show'])
            ->parameters(['productos' => 'producto']);
        Route::get('productos/{producto}/stock', [ProductoController::class, 'stock'])->name('productos.stock');
        Route::post('productos/{producto}/stock/movimientos', [ProductoController::class, 'storeMovimiento'])->name('productos.stock.movimientos.store');

        Route::resource('proveedores', ProveedorController::class)->except(['show'])->parameters(['proveedores' => 'item']);
        Route::resource('categorias', CategoriaProductoController::class)->except(['show'])->parameters(['categorias' => 'item']);
        Route::resource('unidades', UnidadInventarioController::class)->except(['show'])->parameters(['unidades' => 'item']);
        Route::resource('ubicaciones', UbicacionInventarioController::class)->except(['show'])->parameters(['ubicaciones' => 'item']);
    });
