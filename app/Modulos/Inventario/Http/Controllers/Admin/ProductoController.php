<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Inventario\Actions\RegistrarMovimientoInventarioAction;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Http\Requests\GuardarMovimientoInventarioRequest;
use App\Modulos\Inventario\Http\Requests\GuardarProductoRequest;
use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Lista productos con datos de stock para el panel.
     */
    public function index(): View
    {
        return view('modulos.inventario.productos.index', [
            'productos' => Producto::query()
                ->with(['categoria', 'proveedor', 'unidad', 'stock'])
                ->orderBy('nombre')
                ->paginate(15),
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
            'producto' => $producto->load(['categoria', 'unidad', 'stock.ubicacion', 'movimientos.ubicacion', 'movimientos.proveedor']),
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
}
