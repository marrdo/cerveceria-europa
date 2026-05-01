<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Http\Requests\GuardarRecepcionCompraRequest;
use App\Modulos\Compras\Models\LineaPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Compras\Models\RecepcionCompra;
use App\Modulos\Inventario\Actions\RegistrarMovimientoInventarioAction;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\UbicacionInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecepcionCompraController extends Controller
{
    /**
     * Muestra formulario de recepcion de un pedido.
     */
    public function create(PedidoCompra $pedido): View|RedirectResponse
    {
        if (! $pedido->puedeRecibir()) {
            return redirect()->route('admin.compras.pedidos.show', $pedido)
                ->with('status', 'Solo puedes recibir pedidos en estado pedido o recibido parcial.');
        }

        return view('modulos.compras.recepciones.create', [
            'pedido' => $pedido->load(['proveedor', 'lineas.producto.unidad', 'lineas.recepciones']),
            'ubicaciones' => UbicacionInventario::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Confirma la recepcion y genera entradas reales en inventario.
     */
    public function store(
        GuardarRecepcionCompraRequest $request,
        PedidoCompra $pedido,
        RegistrarMovimientoInventarioAction $registrarMovimiento,
    ): RedirectResponse {
        if (! $pedido->puedeRecibir()) {
            return redirect()->route('admin.compras.pedidos.show', $pedido)
                ->with('status', 'Solo puedes recibir pedidos en estado pedido o recibido parcial.');
        }

        DB::transaction(function () use ($request, $pedido, $registrarMovimiento): void {
            $recepcion = RecepcionCompra::query()->create(array_merge($request->datosRecepcion(), [
                'pedido_compra_id' => $pedido->id,
                'numero' => $this->generarNumeroRecepcion($pedido),
                'recibido_por' => $request->user()?->id,
            ]));

            foreach ($request->lineasLimpias() as $linea) {
                /** @var LineaPedidoCompra $lineaPedido */
                $lineaPedido = $pedido->lineas()->with('producto')->findOrFail($linea['linea_pedido_compra_id']);
                $producto = $lineaPedido->producto;

                $movimiento = $registrarMovimiento->execute($producto, [
                    'tipo' => TipoMovimientoInventario::Entrada->value,
                    'proveedor_id' => $pedido->proveedor_id,
                    'ubicacion_inventario_id' => $linea['ubicacion_inventario_id'],
                    'cantidad' => $linea['cantidad'],
                    'coste_unitario' => $lineaPedido->coste_unitario,
                    'motivo' => 'Recepcion de pedido '.$pedido->numero,
                    'referencia' => $recepcion->numero,
                    'codigo_lote' => $linea['codigo_lote'],
                    'caduca_el' => $linea['caduca_el'],
                    'notas' => $linea['notas'],
                ], $request->user()?->id);

                $recepcion->lineas()->create([
                    'linea_pedido_compra_id' => $lineaPedido->id,
                    'producto_id' => $producto->id,
                    'ubicacion_inventario_id' => $linea['ubicacion_inventario_id'],
                    'movimiento_inventario_id' => $movimiento->id,
                    'cantidad' => $linea['cantidad'],
                    'coste_unitario' => $lineaPedido->coste_unitario,
                    'codigo_lote' => $linea['codigo_lote'],
                    'caduca_el' => $linea['caduca_el'],
                    'notas' => $linea['notas'],
                ]);
            }

            $estadoAnterior = $pedido->estado;
            $estadoNuevo = $this->calcularEstadoRecepcion($pedido->fresh(['lineas.recepciones']));

            $pedido->update([
                'estado' => $estadoNuevo,
                'actualizado_por' => $request->user()?->id,
            ]);

            $pedido->eventos()->create([
                'tipo' => 'recepcion',
                'estado_anterior' => $estadoAnterior->value,
                'estado_nuevo' => $estadoNuevo->value,
                'descripcion' => 'Recepcion registrada: '.$recepcion->numero.'.',
                'usuario_id' => $request->user()?->id,
            ]);
        });

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Recepcion registrada correctamente.');
    }

    private function generarNumeroRecepcion(PedidoCompra $pedido): string
    {
        $prefijo = 'RC-'.$pedido->numero.'-';
        $secuencia = RecepcionCompra::query()->where('numero', 'like', $prefijo.'%')->count() + 1;

        return $prefijo.str_pad((string) $secuencia, 3, '0', STR_PAD_LEFT);
    }

    private function calcularEstadoRecepcion(PedidoCompra $pedido): EstadoPedidoCompra
    {
        foreach ($pedido->lineas as $linea) {
            if ($linea->cantidadPendiente() > 0.0005) {
                return EstadoPedidoCompra::RecibidoParcial;
            }
        }

        return EstadoPedidoCompra::Recibido;
    }
}
