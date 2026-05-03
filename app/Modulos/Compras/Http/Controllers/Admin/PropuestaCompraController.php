<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Actions\CrearPedidoCompraBorradorAction;
use App\Modulos\Compras\Http\Requests\GenerarPedidoDesdePropuestaRequest;
use App\Modulos\Inventario\Enums\EstadoStockProducto;
use App\Modulos\Inventario\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PropuestaCompraController extends Controller
{
    /**
     * Muestra propuestas de reposicion agrupadas por proveedor.
     */
    public function index(): View
    {
        $productos = Producto::query()
            ->where('activo', true)
            ->where('controla_stock', true)
            ->with(['proveedor', 'unidad', 'stock'])
            ->orderBy('nombre')
            ->get();

        $productosPropuestos = $productos
            ->filter(fn (Producto $producto): bool => in_array($producto->estadoStock(), [EstadoStockProducto::SinStock, EstadoStockProducto::Bajo], true));

        $productosSinProveedor = $productosPropuestos
            ->filter(fn (Producto $producto): bool => blank($producto->proveedor_id))
            ->values();

        $grupos = $productosPropuestos
            ->filter(fn (Producto $producto): bool => filled($producto->proveedor_id))
            ->groupBy('proveedor_id')
            ->map(fn (Collection $productosProveedor): array => [
                'proveedor' => $productosProveedor->first()->proveedor,
                'productos' => $productosProveedor->map(fn (Producto $producto): array => [
                    'producto' => $producto,
                    'stock_actual' => $producto->cantidadStock(),
                    'cantidad_sugerida' => $this->calcularCantidadSugerida($producto),
                ])->values(),
            ])
            ->sortBy(fn (array $grupo): string => $grupo['proveedor']?->nombre ?? '');

        return view('modulos.compras.propuestas.index', [
            'grupos' => $grupos,
            'productosSinProveedor' => $productosSinProveedor,
        ]);
    }

    /**
     * Genera un pedido borrador desde una propuesta de reposicion.
     */
    public function store(
        GenerarPedidoDesdePropuestaRequest $request,
        CrearPedidoCompraBorradorAction $crearPedido,
    ): RedirectResponse {
        $pedido = $crearPedido->execute([
            'proveedor_id' => $request->proveedorId(),
            'fecha_pedido' => now()->toDateString(),
            'fecha_prevista' => null,
            'notas' => 'Pedido generado desde propuesta de compra por stock bajo.',
        ], $request->lineasLimpias(), $request->user()?->id);

        $pedido->eventos()->create([
            'tipo' => 'propuesta_compra',
            'estado_anterior' => $pedido->estado->value,
            'estado_nuevo' => $pedido->estado->value,
            'descripcion' => 'Pedido generado desde propuesta de compra.',
            'usuario_id' => $request->user()?->id,
        ]);

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Pedido borrador generado desde propuesta de compra.');
    }

    /**
     * Calcula una reposicion simple hasta el doble del umbral de alerta.
     */
    private function calcularCantidadSugerida(Producto $producto): float
    {
        $stockActual = $producto->cantidadStock();
        $alerta = (float) $producto->cantidad_alerta_stock;
        $objetivo = $alerta > 0 ? $alerta * 2 : 1;

        return max(1, round($objetivo - $stockActual, 3));
    }
}
