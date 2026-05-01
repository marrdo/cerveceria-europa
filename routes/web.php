<?php

use App\Http\Controllers\ProfileController;
use App\Modulos\Inventario\Http\Controllers\Admin\CategoriaProductoController;
use App\Modulos\Inventario\Http\Controllers\Admin\InformeInventarioController;
use App\Modulos\Inventario\Http\Controllers\Admin\ProductoController;
use App\Modulos\Inventario\Http\Controllers\Admin\ProveedorController;
use App\Modulos\Inventario\Http\Controllers\Admin\UbicacionInventarioController;
use App\Modulos\Inventario\Http\Controllers\Admin\UnidadInventarioController;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin', function () {
    $productos = Producto::query()->with('stock')->get();

    return view('dashboard', [
        'totalProductos' => $productos->count(),
        'productosBajoStock' => $productos->filter(fn (Producto $producto): bool => $producto->estadoStock()->value === 'bajo')->count(),
        'movimientosRecientes' => MovimientoInventario::query()->where('created_at', '>=', now()->subDays(7))->count(),
    ]);
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin/inventario')->name('admin.inventario.')->group(function (): void {
        Route::get('/', [ProductoController::class, 'index'])->name('index');

        Route::get('alertas', [InformeInventarioController::class, 'alertas'])->name('alertas.index');
        Route::get('alertas/exportar', [InformeInventarioController::class, 'exportarAlertas'])->name('alertas.exportar');
        Route::get('movimientos', [InformeInventarioController::class, 'movimientos'])->name('movimientos.index');
        Route::get('movimientos/exportar', [InformeInventarioController::class, 'exportarMovimientos'])->name('movimientos.exportar');
        Route::get('productos/exportar', [InformeInventarioController::class, 'exportarProductos'])->name('productos.exportar');

        Route::resource('productos', ProductoController::class)
            ->except(['show'])
            ->parameters(['productos' => 'producto']);

        Route::get('productos/{producto}/stock', [ProductoController::class, 'stock'])
            ->name('productos.stock');
        Route::post('productos/{producto}/stock/movimientos', [ProductoController::class, 'storeMovimiento'])
            ->name('productos.stock.movimientos.store');

        Route::resource('proveedores', ProveedorController::class)
            ->except(['show'])
            ->parameters(['proveedores' => 'item']);
        Route::resource('categorias', CategoriaProductoController::class)
            ->except(['show'])
            ->parameters(['categorias' => 'item']);
        Route::resource('unidades', UnidadInventarioController::class)
            ->except(['show'])
            ->parameters(['unidades' => 'item']);
        Route::resource('ubicaciones', UbicacionInventarioController::class)
            ->except(['show'])
            ->parameters(['ubicaciones' => 'item']);
    });
});

require __DIR__.'/auth.php';
