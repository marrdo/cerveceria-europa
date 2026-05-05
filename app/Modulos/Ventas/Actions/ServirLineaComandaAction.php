<?php

namespace App\Modulos\Ventas\Actions;

use App\Modulos\Inventario\Actions\RegistrarMovimientoInventarioAction;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Models\LineaComanda;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServirLineaComandaAction
{
    public function __construct(
        private readonly RegistrarMovimientoInventarioAction $registrarMovimientoInventario,
    ) {
    }

    /**
     * Marca una linea como servida y descuenta stock si esta vinculada a un producto inventariable.
     */
    public function execute(LineaComanda $linea, string $usuarioId): LineaComanda
    {
        return DB::transaction(function () use ($linea, $usuarioId): LineaComanda {
            $linea = LineaComanda::query()
                ->with(['comanda', 'producto'])
                ->lockForUpdate()
                ->findOrFail($linea->id);

            if ($linea->estaServida()) {
                return $linea;
            }

            if ($linea->estado === EstadoLineaComanda::Cancelada) {
                throw ValidationException::withMessages([
                    'estado' => 'No puedes servir una linea cancelada.',
                ]);
            }

            $comanda = $linea->comanda;
            $producto = $linea->producto;

            if ($producto?->controla_stock) {
                if (blank($comanda->ubicacion_inventario_id)) {
                    throw ValidationException::withMessages([
                        'ubicacion_inventario_id' => 'La comanda necesita una ubicacion de inventario para descontar stock.',
                    ]);
                }

                $movimiento = $this->registrarMovimientoInventario->execute($producto, [
                    'tipo' => TipoMovimientoInventario::Salida->value,
                    'cantidad' => (float) $linea->cantidad,
                    'ubicacion_inventario_id' => $comanda->ubicacion_inventario_id,
                    'motivo' => "Venta servida en comanda {$comanda->numero}",
                    'referencia' => $comanda->numero,
                    'notas' => $linea->nombre,
                ], $usuarioId);

                $linea->movimiento_inventario_id = $movimiento->id;
            }

            $linea->estado = EstadoLineaComanda::Servida;
            $linea->servida_at = now();
            $linea->save();

            $comanda->update([
                'estado' => $comanda->lineas()->where('id', '!=', $linea->id)->where('estado', '!=', EstadoLineaComanda::Servida->value)->exists()
                    ? EstadoComanda::EnPreparacion
                    : EstadoComanda::Servida,
                'servida_at' => now(),
                'actualizado_por' => $usuarioId,
            ]);

            return $linea->refresh();
        });
    }
}
