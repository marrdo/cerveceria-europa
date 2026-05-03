<?php

namespace App\Modulos\Compras\Actions;

use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Inventario\Models\Producto;
use Illuminate\Support\Facades\DB;

class CrearPedidoCompraBorradorAction
{
    /**
     * Crea un pedido de compra en borrador con lineas, totales y evento inicial.
     *
     * @param array<string, mixed> $datosPedido
     * @param array<int, array<string, mixed>> $lineas
     */
    public function execute(array $datosPedido, array $lineas, ?string $usuarioId): PedidoCompra
    {
        return DB::transaction(function () use ($datosPedido, $lineas, $usuarioId): PedidoCompra {
            $pedido = PedidoCompra::query()->create(array_merge($datosPedido, [
                'numero' => $this->generarNumeroPedido(),
                'estado' => EstadoPedidoCompra::Borrador,
                'creado_por' => $usuarioId,
                'actualizado_por' => $usuarioId,
            ]));

            $this->guardarLineas($pedido, $lineas);
            $this->recalcularTotales($pedido);

            $pedido->eventos()->create([
                'tipo' => 'creado',
                'descripcion' => 'Pedido creado en borrador.',
                'usuario_id' => $usuarioId,
            ]);

            return $pedido;
        });
    }

    private function generarNumeroPedido(): string
    {
        $anio = now()->year;
        $ultimoNumero = PedidoCompra::query()
            ->where('numero', 'like', "PC-%-{$anio}")
            ->orderByDesc('numero')
            ->lockForUpdate()
            ->value('numero');

        $secuencia = 1;

        if (is_string($ultimoNumero) && preg_match('/^PC-(\d{5})-'.$anio.'$/', $ultimoNumero, $coincidencias) === 1) {
            $secuencia = ((int) $coincidencias[1]) + 1;
        }

        return 'PC-'.str_pad((string) $secuencia, 5, '0', STR_PAD_LEFT).'-'.$anio;
    }

    /**
     * @param array<int, array<string, mixed>> $lineas
     */
    private function guardarLineas(PedidoCompra $pedido, array $lineas): void
    {
        foreach ($lineas as $orden => $linea) {
            $producto = Producto::query()->findOrFail($linea['producto_id']);
            $subtotal = round($linea['cantidad'] * $linea['coste_unitario'], 2);
            $impuestos = round($subtotal * ($linea['iva_porcentaje'] / 100), 2);

            $pedido->lineas()->create([
                'producto_id' => $producto->id,
                'descripcion' => $linea['descripcion'] !== '' ? $linea['descripcion'] : $producto->nombre,
                'cantidad' => $linea['cantidad'],
                'coste_unitario' => $linea['coste_unitario'],
                'iva_porcentaje' => $linea['iva_porcentaje'],
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => round($subtotal + $impuestos, 2),
                'orden' => $orden,
            ]);
        }
    }

    private function recalcularTotales(PedidoCompra $pedido): void
    {
        $lineas = $pedido->lineas()->get();

        $pedido->update([
            'subtotal' => round((float) $lineas->sum('subtotal'), 2),
            'impuestos' => round((float) $lineas->sum('impuestos'), 2),
            'total' => round((float) $lineas->sum('total'), 2),
        ]);
    }
}
