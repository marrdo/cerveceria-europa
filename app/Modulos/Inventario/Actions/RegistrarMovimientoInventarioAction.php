<?php

namespace App\Modulos\Inventario\Actions;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\LoteInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\StockInventario;
use Illuminate\Support\Carbon;
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

            if ($tipo === TipoMovimientoInventario::Salida) {
                $this->consumirLotes($producto, $ubicacionId, $cantidad, $producto->controla_caducidad);
            }

            $movimiento = $producto->movimientos()->create([
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

            if ($tipo === TipoMovimientoInventario::Entrada) {
                $lote = $this->crearLoteEntrada($producto, $movimiento, $ubicacionId, $cantidad, $datos);
                $movimiento->update(['lote_inventario_id' => $lote->id]);
            }

            return $movimiento;
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

        $lotesMovidos = $this->moverLotes($producto, $origenId, $destinoId, $cantidad);

        $origen->update(['cantidad' => $origenDespues]);
        $destino->update(['cantidad' => $destinoDespues]);

        return $producto->movimientos()->create([
            'ubicacion_origen_id' => $origenId,
            'ubicacion_destino_id' => $destinoId,
            'lote_inventario_id' => $lotesMovidos[0]->id ?? null,
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

    /**
     * Crea un lote a partir de una entrada manual.
     *
     * @param array<string, mixed> $datos
     */
    private function crearLoteEntrada(Producto $producto, MovimientoInventario $movimiento, string $ubicacionId, float $cantidad, array $datos): LoteInventario
    {
        return LoteInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacionId,
            'proveedor_id' => $datos['proveedor_id'] ?? null,
            'movimiento_entrada_id' => $movimiento->id,
            'codigo_lote' => $datos['codigo_lote'] ?? null,
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'recibido_el' => now()->toDateString(),
            'caduca_el' => $datos['caduca_el'] ?? null,
            'activo' => true,
        ]);
    }

    /**
     * Consume lotes en salida. FEFO si hay caducidad, FIFO si no la hay.
     */
    private function consumirLotes(Producto $producto, string $ubicacionId, float $cantidad, bool $exigirCobertura): void
    {
        $restante = $cantidad;
        $lotes = $this->consultaLotesDisponibles($producto, $ubicacionId)->lockForUpdate()->get();

        foreach ($lotes as $lote) {
            if ($restante <= 0.0005) {
                break;
            }

            $disponible = $this->normalizarCantidad($lote->cantidad_disponible);
            $consumo = min($restante, $disponible);

            $lote->update([
                'cantidad_disponible' => $this->normalizarCantidad($disponible - $consumo),
            ]);

            $restante = $this->normalizarCantidad($restante - $consumo);
        }

        if ($exigirCobertura && $restante > 0.0005) {
            throw ValidationException::withMessages([
                'cantidad' => 'No hay suficiente cantidad disponible en lotes para este producto con caducidad.',
            ]);
        }
    }

    /**
     * Mueve lotes entre ubicaciones para mantener trazabilidad en transferencias.
     *
     * @return array<int, LoteInventario>
     */
    private function moverLotes(Producto $producto, string $origenId, string $destinoId, float $cantidad): array
    {
        $restante = $cantidad;
        $movidos = [];
        $lotes = $this->consultaLotesDisponibles($producto, $origenId)->lockForUpdate()->get();

        foreach ($lotes as $loteOrigen) {
            if ($restante <= 0.0005) {
                break;
            }

            $disponible = $this->normalizarCantidad($loteOrigen->cantidad_disponible);
            $cantidadMover = min($restante, $disponible);

            $loteOrigen->update([
                'cantidad_disponible' => $this->normalizarCantidad($disponible - $cantidadMover),
            ]);

            $loteDestino = $this->crearLoteTraslado($loteOrigen, $destinoId, $cantidadMover);
            $movidos[] = $loteDestino;
            $restante = $this->normalizarCantidad($restante - $cantidadMover);
        }

        if ($producto->controla_caducidad && $restante > 0.0005) {
            throw ValidationException::withMessages([
                'cantidad' => 'No hay suficiente cantidad disponible en lotes para transferir este producto con caducidad.',
            ]);
        }

        return $movidos;
    }

    private function crearLoteTraslado(LoteInventario $loteOrigen, string $destinoId, float $cantidad): LoteInventario
    {
        return LoteInventario::query()->create([
            'producto_id' => $loteOrigen->producto_id,
            'ubicacion_inventario_id' => $destinoId,
            'proveedor_id' => $loteOrigen->proveedor_id,
            'movimiento_entrada_id' => $loteOrigen->movimiento_entrada_id,
            'codigo_lote' => $loteOrigen->codigo_lote,
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'recibido_el' => Carbon::parse($loteOrigen->recibido_el)->toDateString(),
            'caduca_el' => $loteOrigen->caduca_el?->toDateString(),
            'activo' => true,
        ]);
    }

    /**
     * Ordena por caducidad si existe y despues por recepcion para cubrir FEFO/FIFO.
     */
    private function consultaLotesDisponibles(Producto $producto, string $ubicacionId): mixed
    {
        return LoteInventario::query()
            ->where('producto_id', $producto->id)
            ->where('ubicacion_inventario_id', $ubicacionId)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderByRaw('caduca_el is null')
            ->orderBy('caduca_el')
            ->orderBy('recibido_el')
            ->orderBy('created_at');
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
