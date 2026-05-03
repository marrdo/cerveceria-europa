<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Http\Requests\GuardarDevolucionProveedorRequest;
use App\Modulos\Compras\Models\DevolucionProveedor;
use App\Modulos\Compras\Models\LineaPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Inventario\Actions\RegistrarMovimientoInventarioAction;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DevolucionProveedorController extends Controller
{
    /**
     * Registra una devolucion a proveedor y descuenta stock real.
     */
    public function store(
        GuardarDevolucionProveedorRequest $request,
        PedidoCompra $pedido,
        RegistrarMovimientoInventarioAction $registrarMovimiento,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $pedido, $registrarMovimiento): void {
            $devolucion = DevolucionProveedor::query()->create(array_merge($request->datosDevolucion(), [
                'pedido_compra_id' => $pedido->id,
                'proveedor_id' => $pedido->proveedor_id,
                'numero' => $this->generarNumeroDevolucion($pedido),
                'registrada_por' => $request->user()?->id,
            ]));

            $datosLinea = $request->datosLinea();
            /** @var LineaPedidoCompra $lineaPedido */
            $lineaPedido = $pedido->lineas()->with('producto')->findOrFail($datosLinea['linea_pedido_compra_id']);
            $producto = $lineaPedido->producto;

            $movimiento = $registrarMovimiento->execute($producto, [
                'tipo' => TipoMovimientoInventario::Salida->value,
                'proveedor_id' => $pedido->proveedor_id,
                'ubicacion_inventario_id' => $datosLinea['ubicacion_inventario_id'],
                'cantidad' => $datosLinea['cantidad'],
                'coste_unitario' => $lineaPedido->coste_unitario,
                'motivo' => 'Devolucion a proveedor del pedido '.$pedido->numero,
                'referencia' => $devolucion->numero,
                'notas' => $datosLinea['notas'] ?? $devolucion->motivo,
            ], $request->user()?->id);

            $devolucion->lineas()->create([
                'linea_pedido_compra_id' => $lineaPedido->id,
                'producto_id' => $producto->id,
                'ubicacion_inventario_id' => $datosLinea['ubicacion_inventario_id'],
                'movimiento_inventario_id' => $movimiento->id,
                'cantidad' => $datosLinea['cantidad'],
                'coste_unitario' => $lineaPedido->coste_unitario,
                'notas' => $datosLinea['notas'],
            ]);

            $pedido->eventos()->create([
                'tipo' => 'devolucion_proveedor',
                'estado_anterior' => $pedido->estado->value,
                'estado_nuevo' => $pedido->estado->value,
                'descripcion' => 'Devolucion registrada: '.$devolucion->numero.'.',
                'usuario_id' => $request->user()?->id,
            ]);
        });

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Devolucion a proveedor registrada correctamente.');
    }

    /**
     * Genera una secuencia de devolucion vinculada al pedido.
     *
     * Ejemplo: DP-PC-00001-2026-001.
     */
    private function generarNumeroDevolucion(PedidoCompra $pedido): string
    {
        $prefijo = 'DP-'.$pedido->numero.'-';
        $secuencia = DevolucionProveedor::query()->where('numero', 'like', $prefijo.'%')->count() + 1;

        return $prefijo.str_pad((string) $secuencia, 3, '0', STR_PAD_LEFT);
    }
}
