<?php

use App\Modulos\Compras\Http\Controllers\Admin\BorradorCompraDocumentoController;
use App\Modulos\Compras\Http\Controllers\Admin\DevolucionProveedorController;
use App\Modulos\Compras\Http\Controllers\Admin\DocumentoCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\IncidenciaRecepcionCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\PedidoCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\PropuestaCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\RecepcionCompraController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'modulo:compras'])
    ->prefix('admin/compras')
    ->name('admin.compras.')
    ->group(function (): void {
        Route::get('/', [PedidoCompraController::class, 'index'])->name('index');
        Route::get('documentos/borradores/{borrador}/edit', [BorradorCompraDocumentoController::class, 'edit'])->name('documentos.borradores.edit');
        Route::post('documentos/borradores/{borrador}', [BorradorCompraDocumentoController::class, 'update'])->name('documentos.borradores.update');
        Route::post('documentos/borradores/{borrador}/generar-pedido', [BorradorCompraDocumentoController::class, 'generarPedido'])->name('documentos.borradores.generar-pedido');
        Route::resource('documentos', DocumentoCompraController::class)->only(['index', 'create', 'store', 'show', 'destroy'])->parameters(['documentos' => 'documento']);
        Route::get('propuestas', [PropuestaCompraController::class, 'index'])->name('propuestas.index');
        Route::get('propuestas/exportar', [PropuestaCompraController::class, 'exportar'])->name('propuestas.exportar');
        Route::post('propuestas', [PropuestaCompraController::class, 'store'])->name('propuestas.store');
        Route::patch('pedidos/{pedido}/estado', [PedidoCompraController::class, 'cambiarEstado'])->name('pedidos.estado');
        Route::patch('pedidos/{pedido}/cerrar-pendiente', [PedidoCompraController::class, 'cerrarPendiente'])->name('pedidos.cerrar-pendiente');
        Route::post('pedidos/{pedido}/incidencias', [IncidenciaRecepcionCompraController::class, 'store'])->name('pedidos.incidencias.store');
        Route::post('pedidos/{pedido}/devoluciones', [DevolucionProveedorController::class, 'store'])->name('pedidos.devoluciones.store');
        Route::get('pedidos/{pedido}/recepciones/create', [RecepcionCompraController::class, 'create'])->name('pedidos.recepciones.create');
        Route::post('pedidos/{pedido}/recepciones', [RecepcionCompraController::class, 'store'])->name('pedidos.recepciones.store');
        Route::resource('pedidos', PedidoCompraController::class)->except(['destroy'])->parameters(['pedidos' => 'pedido']);
    });
