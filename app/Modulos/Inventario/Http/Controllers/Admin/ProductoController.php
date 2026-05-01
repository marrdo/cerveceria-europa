<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Inventario\Actions\RegistrarMovimientoInventarioAction;
use App\Modulos\Inventario\Enums\EstadoStockProducto;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Http\Requests\GuardarMovimientoInventarioRequest;
use App\Modulos\Inventario\Http\Requests\GuardarProductoRequest;
use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Lista productos con datos de stock para el panel.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'busqueda' => trim((string) $request->query('busqueda', '')),
            'categoria_producto_id' => (string) $request->query('categoria_producto_id', ''),
            'proveedor_id' => (string) $request->query('proveedor_id', ''),
            'estado_stock' => (string) $request->query('estado_stock', ''),
            'activo' => (string) $request->query('activo', ''),
        ];

        $productos = Producto::query()
            ->with(['categoria', 'proveedor', 'unidad', 'stock'])
            ->withSum('stock as cantidad_stock_total', 'cantidad')
            ->when($filtros['busqueda'] !== '', function ($query) use ($filtros): void {
                $busqueda = $filtros['busqueda'];

                $query->where(function ($subquery) use ($busqueda): void {
                    $subquery
                        ->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('sku', 'like', "%{$busqueda}%")
                        ->orWhere('codigo_barras', 'like', "%{$busqueda}%")
                        ->orWhere('referencia_proveedor', 'like', "%{$busqueda}%");
                });
            })
            ->when($filtros['categoria_producto_id'] !== '', fn ($query) => $query->where('categoria_producto_id', $filtros['categoria_producto_id']))
            ->when($filtros['proveedor_id'] !== '', fn ($query) => $query->where('proveedor_id', $filtros['proveedor_id']))
            ->when($filtros['activo'] !== '', fn ($query) => $query->where('activo', $filtros['activo'] === '1'))
            ->when($filtros['estado_stock'] !== '', fn ($query) => $this->aplicarFiltroEstadoStock($query, $filtros['estado_stock']))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('modulos.inventario.productos.index', [
            'productos' => $productos,
            'categorias' => CategoriaProducto::query()->where('activo', true)->orderBy('nombre')->get(),
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'estadosStock' => EstadoStockProducto::cases(),
            'filtros' => $filtros,
        ]);
    }

    /**
     * Muestra el formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.inventario.productos.create', $this->opcionesFormulario());
    }

    /**
     * Guarda un producto nuevo.
     */
    public function store(GuardarProductoRequest $request): RedirectResponse
    {
        Producto::query()->create($request->datos());

        return redirect()->route('admin.inventario.productos.index')
            ->with('status', 'Producto creado correctamente.');
    }

    /**
     * Muestra el formulario de edicion.
     */
    public function edit(Producto $producto): View
    {
        return view('modulos.inventario.productos.edit', array_merge(
            $this->opcionesFormulario(),
            ['producto' => $producto],
        ));
    }

    /**
     * Actualiza un producto existente.
     */
    public function update(GuardarProductoRequest $request, Producto $producto): RedirectResponse
    {
        $producto->update($request->datos());

        return redirect()->route('admin.inventario.productos.index')
            ->with('status', 'Producto actualizado correctamente.');
    }

    /**
     * Elimina logicamente un producto.
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('admin.inventario.productos.index')
            ->with('status', 'Producto eliminado correctamente.');
    }

    /**
     * Muestra stock y movimientos de un producto.
     */
    public function stock(Producto $producto): View
    {
        return view('modulos.inventario.productos.stock', [
            'producto' => $producto->load(['categoria', 'unidad', 'stock.ubicacion', 'lotes.ubicacion', 'movimientos.ubicacion', 'movimientos.proveedor']),
            'ubicaciones' => UbicacionInventario::query()->where('activo', true)->orderBy('nombre')->get(),
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'tiposMovimiento' => TipoMovimientoInventario::cases(),
        ]);
    }

    /**
     * Registra un movimiento manual de stock.
     */
    public function storeMovimiento(
        GuardarMovimientoInventarioRequest $request,
        Producto $producto,
        RegistrarMovimientoInventarioAction $registrarMovimiento,
    ): RedirectResponse {
        $registrarMovimiento->execute($producto, $request->validated(), $request->user()?->id);

        return redirect()->route('admin.inventario.productos.stock', $producto)
            ->with('status', 'Movimiento registrado correctamente.');
    }

    /**
     * Opciones comunes para formularios de producto.
     *
     * @return array<string, mixed>
     */
    private function opcionesFormulario(): array
    {
        return [
            'categorias' => CategoriaProducto::query()->where('activo', true)->orderBy('nombre')->get(),
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'unidades' => UnidadInventario::query()->where('activo', true)->orderBy('nombre')->get(),
        ];
    }

    /**
     * Aplica filtros calculados de estado de stock sobre el agregado de stock.
     */
    private function aplicarFiltroEstadoStock(mixed $query, string $estado): void
    {
        $stockTotalSql = '(select coalesce(sum(stock_inventario.cantidad), 0) from stock_inventario where stock_inventario.producto_id = productos.id)';

        match ($estado) {
            EstadoStockProducto::SinControl->value => $query->where('controla_stock', false),
            EstadoStockProducto::SinStock->value => $query
                ->where('controla_stock', true)
                ->whereRaw("{$stockTotalSql} <= 0"),
            EstadoStockProducto::Bajo->value => $query
                ->where('controla_stock', true)
                ->where('cantidad_alerta_stock', '>', 0)
                ->whereRaw("{$stockTotalSql} > 0")
                ->whereRaw("{$stockTotalSql} <= cantidad_alerta_stock"),
            EstadoStockProducto::Correcto->value => $query
                ->where('controla_stock', true)
                ->whereRaw("{$stockTotalSql} > 0")
                ->whereRaw("(cantidad_alerta_stock <= 0 OR {$stockTotalSql} > cantidad_alerta_stock)"),
            default => null,
        };
    }
}
