<?php

namespace App\Modulos\Inventario\Actions;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\StockInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarMovimientoInventarioAction
{
    /**
     * Registra una entrada, salida, ajuste o transferencia y actualiza el stock.
     *
     * En ajustes, `cantidad` representa el stock final deseado.
     *
     * @param array<string, mixed> $datos
     */
    public function execute(Producto $producto, array $datos, ?string $creadoPor = null): MovimientoInventario
    {
        return DB::transaction(function () use ($producto, $datos, $creadoPor): MovimientoInventario {
            if (! $producto->controla_stock) {
                throw ValidationException::withMessages([
                    'tipo' => 'Este producto no tiene activado el control de stock.',
                ]);
            }

            $tipo = TipoMovimientoInventario::from((string) $datos['tipo']);
            $cantidad = $this->normalizarCantidad($datos['cantidad']);

            if ($tipo === TipoMovimientoInventario::Transferencia) {
                return $this->registrarTransferencia($producto, $datos, $cantidad, $creadoPor);
            }

            $ubicacionId = (string) $datos['ubicacion_inventario_id'];
            $stock = StockInventario::query()
                ->where('producto_id', $producto->id)
                ->where('ubicacion_inventario_id', $ubicacionId)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['producto_id' => $producto->id, 'ubicacion_inventario_id' => $ubicacionId],
                    ['cantidad' => 0, 'cantidad_minima' => 0],
                );

            $stockAntes = $this->normalizarCantidad($stock->cantidad);
            $stockDespues = match ($tipo) {
                TipoMovimientoInventario::Entrada => $stockAntes + $cantidad,
                TipoMovimientoInventario::Salida => $this->calcularSalida($producto, $stockAntes, $cantidad),
                TipoMovimientoInventario::Ajuste => $cantidad,
                TipoMovimientoInventario::Transferencia => $stockAntes,
            };
            $stockDespues = $this->normalizarCantidad($stockDespues);

            $stock->update(['cantidad' => $stockDespues]);

            return $producto->movimientos()->create([
                'proveedor_id' => $datos['proveedor_id'] ?? null,
                'ubicacion_inventario_id' => $ubicacionId,
                'tipo' => $tipo,
                'cantidad' => $tipo === TipoMovimientoInventario::Ajuste ? abs($stockDespues - $stockAntes) : $cantidad,
                'stock_antes' => $stockAntes,
                'stock_despues' => $stockDespues,
                'coste_unitario' => $datos['coste_unitario'] ?? null,
                'motivo' => $datos['motivo'],
                'referencia' => $datos['referencia'] ?? null,
                'caduca_el' => $datos['caduca_el'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'creado_por' => $creadoPor,
            ]);
        });
    }

    /**
     * Registra una transferencia entre dos ubicaciones.
     *
     * @param array<string, mixed> $datos
     */
    private function registrarTransferencia(Producto $producto, array $datos, float $cantidad, ?string $creadoPor): MovimientoInventario
    {
        $origenId = (string) $datos['ubicacion_origen_id'];
        $destinoId = (string) $datos['ubicacion_destino_id'];

        if ($origenId === $destinoId) {
            throw ValidationException::withMessages([
                'ubicacion_destino_id' => 'La ubicacion destino debe ser distinta de la ubicacion origen.',
            ]);
        }

        $origen = $this->stockPorUbicacion($producto, $origenId);
        $destino = $this->stockPorUbicacion($producto, $destinoId);

        $origenAntes = $this->normalizarCantidad($origen->cantidad);
        $destinoAntes = $this->normalizarCantidad($destino->cantidad);
        $origenDespues = $this->normalizarCantidad($this->calcularSalida($producto, $origenAntes, $cantidad));
        $destinoDespues = $this->normalizarCantidad($destinoAntes + $cantidad);

        $origen->update(['cantidad' => $origenDespues]);
        $destino->update(['cantidad' => $destinoDespues]);

        return $producto->movimientos()->create([
            'ubicacion_origen_id' => $origenId,
            'ubicacion_destino_id' => $destinoId,
            'tipo' => TipoMovimientoInventario::Transferencia,
            'cantidad' => $cantidad,
            'stock_antes' => $origenAntes,
            'stock_despues' => $origenDespues,
            'motivo' => $datos['motivo'],
            'referencia' => $datos['referencia'] ?? null,
            'notas' => $datos['notas'] ?? null,
            'creado_por' => $creadoPor,
        ]);
    }

    private function stockPorUbicacion(Producto $producto, string $ubicacionId): StockInventario
    {
        return StockInventario::query()
            ->where('producto_id', $producto->id)
            ->where('ubicacion_inventario_id', $ubicacionId)
            ->lockForUpdate()
            ->firstOrCreate(
                ['producto_id' => $producto->id, 'ubicacion_inventario_id' => $ubicacionId],
                ['cantidad' => 0, 'cantidad_minima' => 0],
            );
    }

    private function calcularSalida(Producto $producto, float $stockAntes, float $cantidad): float
    {
        if ($cantidad - $stockAntes > 0.0005) {
            throw ValidationException::withMessages([
                'cantidad' => "No puedes sacar {$producto->formatearCantidad($cantidad)} {$producto->codigoUnidad()} de {$producto->nombre}; solo hay {$producto->formatearCantidad($stockAntes)}.",
            ]);
        }

        return $stockAntes - $cantidad;
    }

    private function normalizarCantidad(mixed $cantidad): float
    {
        return round((float) $cantidad, 3);
    }
}
