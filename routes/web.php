<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ModuloController;
use App\Http\Controllers\Admin\PersonalController;
use App\Models\Modulo;
use App\Modulos\Compras\Http\Controllers\Admin\BorradorCompraDocumentoController;
use App\Modulos\Compras\Http\Controllers\Admin\DevolucionProveedorController;
use App\Modulos\Compras\Http\Controllers\Admin\DocumentoCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\IncidenciaRecepcionCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\PedidoCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\PropuestaCompraController;
use App\Modulos\Compras\Http\Controllers\Admin\RecepcionCompraController;
use App\Modulos\Espacios\Http\Controllers\Admin\MesaController;
use App\Modulos\Espacios\Http\Controllers\Admin\RecintoController;
use App\Modulos\Espacios\Http\Controllers\Admin\ZonaController;
use App\Modulos\Inventario\Http\Controllers\Admin\CategoriaProductoController;
use App\Modulos\Inventario\Http\Controllers\Admin\InformeInventarioController;
use App\Modulos\Inventario\Http\Controllers\Admin\ProductoController;
use App\Modulos\Inventario\Http\Controllers\Admin\ProveedorController;
use App\Modulos\Inventario\Http\Controllers\Admin\UbicacionInventarioController;
use App\Modulos\Inventario\Http\Controllers\Admin\UnidadInventarioController;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Ventas\Http\Controllers\Admin\ComandaController;
use App\Modulos\WebPublica\Http\Controllers\Admin\ContenidoWebController;
use App\Modulos\WebPublica\Http\Controllers\Admin\CategoriaBlogController;
use App\Modulos\WebPublica\Http\Controllers\Admin\CategoriaCartaController;
use App\Modulos\WebPublica\Http\Controllers\Admin\PostBlogController;
use App\Modulos\WebPublica\Http\Controllers\Admin\SeccionWebController;
use App\Modulos\WebPublica\Http\Controllers\Publico\BlogPublicoController;
use App\Modulos\WebPublica\Http\Controllers\Publico\CartaPublicaController;
use App\Modulos\WebPublica\Http\Controllers\Publico\FueraCartaPublicaController;
use App\Modulos\WebPublica\Http\Controllers\Publico\WebPublicaController;
use Illuminate\Support\Facades\Route;

Route::middleware('web_publica.activa')->group(function (): void {
    Route::get('/', function () {
        return app(WebPublicaController::class)->inicio();
    })->name('web.inicio');
    Route::get('/carta', [CartaPublicaController::class, 'index'])->name('web.carta');
    Route::get('/cervezas', [CartaPublicaController::class, 'cervezas'])->name('web.cervezas');
    Route::get('/fuera-de-carta', [FueraCartaPublicaController::class, 'index'])->name('web.fuera-carta');
    Route::get('/recomendaciones', [WebPublicaController::class, 'recomendaciones'])->name('web.recomendaciones');
    Route::get('/blog', [BlogPublicoController::class, 'index'])->name('web.blog');
    Route::get('/blog/categoria/{categoria:slug}', [BlogPublicoController::class, 'categoria'])->name('web.blog.categoria');
    Route::get('/blog/{post:slug}', [BlogPublicoController::class, 'show'])->name('web.blog.show');
    Route::get('/contacto', [WebPublicaController::class, 'contacto'])->name('web.contacto');
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
        'modulos' => Modulo::query()->orderBy('grupo')->orderBy('orden')->orderBy('nombre')->get(),
    ]);
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/admin/modulos/{modulo}/toggle', [ModuloController::class, 'toggle'])->name('admin.modulos.toggle');

    Route::prefix('admin/personal')->name('admin.personal.')->middleware('modulo:personal')->group(function (): void {
        Route::get('/', [PersonalController::class, 'index'])->name('index');
        Route::get('usuarios/create', [PersonalController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [PersonalController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}', [PersonalController::class, 'show'])->name('usuarios.show');
        Route::get('usuarios/{usuario}/edit', [PersonalController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [PersonalController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [PersonalController::class, 'destroy'])->name('usuarios.destroy');
    });

    Route::prefix('admin/inventario')->name('admin.inventario.')->middleware('modulo:inventario')->group(function (): void {
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

    Route::prefix('admin/compras')->name('admin.compras.')->middleware('modulo:compras')->group(function (): void {
        Route::get('/', [PedidoCompraController::class, 'index'])->name('index');
        Route::get('documentos/borradores/{borrador}/edit', [BorradorCompraDocumentoController::class, 'edit'])->name('documentos.borradores.edit');
        Route::post('documentos/borradores/{borrador}', [BorradorCompraDocumentoController::class, 'update'])->name('documentos.borradores.update');
        Route::post('documentos/borradores/{borrador}/generar-pedido', [BorradorCompraDocumentoController::class, 'generarPedido'])->name('documentos.borradores.generar-pedido');
        Route::resource('documentos', DocumentoCompraController::class)
            ->only(['index', 'create', 'store', 'show', 'destroy'])
            ->parameters(['documentos' => 'documento']);
        Route::get('propuestas', [PropuestaCompraController::class, 'index'])->name('propuestas.index');
        Route::post('propuestas', [PropuestaCompraController::class, 'store'])->name('propuestas.store');
        Route::patch('pedidos/{pedido}/estado', [PedidoCompraController::class, 'cambiarEstado'])->name('pedidos.estado');
        Route::patch('pedidos/{pedido}/cerrar-pendiente', [PedidoCompraController::class, 'cerrarPendiente'])->name('pedidos.cerrar-pendiente');
        Route::post('pedidos/{pedido}/incidencias', [IncidenciaRecepcionCompraController::class, 'store'])->name('pedidos.incidencias.store');
        Route::post('pedidos/{pedido}/devoluciones', [DevolucionProveedorController::class, 'store'])->name('pedidos.devoluciones.store');
        Route::get('pedidos/{pedido}/recepciones/create', [RecepcionCompraController::class, 'create'])->name('pedidos.recepciones.create');
        Route::post('pedidos/{pedido}/recepciones', [RecepcionCompraController::class, 'store'])->name('pedidos.recepciones.store');
        Route::resource('pedidos', PedidoCompraController::class)
            ->except(['destroy'])
            ->parameters(['pedidos' => 'pedido']);
    });

    Route::prefix('admin/ventas')->name('admin.ventas.')->middleware('modulo:ventas')->group(function (): void {
        Route::get('/', [ComandaController::class, 'index'])->name('index');
        Route::patch('comandas/{comanda}/servir', [ComandaController::class, 'servir'])->name('comandas.servir');
        Route::patch('comandas/{comanda}/cancelar', [ComandaController::class, 'cancelar'])->name('comandas.cancelar');
        Route::patch('comandas/{comanda}/operativa', [ComandaController::class, 'actualizarOperativa'])->name('comandas.operativa.update');
        Route::post('comandas/{comanda}/lineas', [ComandaController::class, 'agregarLineas'])->name('comandas.lineas.store');
        Route::post('comandas/{comanda}/pagos', [ComandaController::class, 'cobrar'])->name('comandas.pagos.store');
        Route::patch('comandas/{comanda}/lineas/{linea}/servir', [ComandaController::class, 'servirLinea'])->name('comandas.lineas.servir');
        Route::resource('comandas', ComandaController::class)
            ->only(['index', 'create', 'store', 'show']);
    });

    Route::prefix('admin/espacios')->name('admin.espacios.')->middleware('modulo:espacios')->group(function (): void {
        Route::get('/', fn () => redirect()->route('admin.espacios.recintos.index'))->name('index');
        Route::resource('recintos', RecintoController::class)->except(['show']);
        Route::resource('zonas', ZonaController::class)->except(['show']);
        Route::resource('mesas', MesaController::class)->except(['show']);
    });

    Route::prefix('admin/web-publica')->name('admin.web-publica.')->middleware('modulo:web_publica')->group(function (): void {
        Route::patch('contenidos/{contenido}/toggle/{campo}', [ContenidoWebController::class, 'toggle'])->name('contenidos.toggle');
        Route::resource('contenidos', ContenidoWebController::class)
            ->except(['show'])
            ->parameters(['contenidos' => 'contenido']);
        Route::resource('secciones', SeccionWebController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['secciones' => 'seccion']);
        Route::resource('carta-categorias', CategoriaCartaController::class)
            ->except(['show'])
            ->parameters(['carta-categorias' => 'categoria']);
        Route::resource('blog-categorias', CategoriaBlogController::class)
            ->except(['show'])
            ->parameters(['blog-categorias' => 'categoria']);
        Route::patch('blog/{post}/toggle/{campo}', [PostBlogController::class, 'toggle'])->name('blog.toggle');
        Route::resource('blog', PostBlogController::class)
            ->except(['show'])
            ->parameters(['blog' => 'post']);
    });
});

require __DIR__.'/auth.php';
