<?php

namespace App\Modulos\Ventas\Actions;

use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\Ventas\Models\PagoComanda;
use App\Modulos\Ventas\Models\TurnoCaja;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarPagoComandaAction
{
    /**
     * Registra un pago de comanda y cierra la comanda como pagada cuando cubre el total.
     *
     * @param array<string, mixed> $datos
     */
    public function execute(Comanda $comanda, array $datos, string $usuarioId): PagoComanda
    {
        return DB::transaction(function () use ($comanda, $datos, $usuarioId): PagoComanda {
            $comanda = Comanda::query()
                ->with('pagos')
                ->lockForUpdate()
                ->findOrFail($comanda->id);

            if ($comanda->estado !== EstadoComanda::Servida) {
                throw ValidationException::withMessages([
                    'estado' => 'Solo puedes cobrar una comanda servida.',
                ]);
            }

            $pendiente = $comanda->pendientePago();

            if ($pendiente <= 0.005) {
                throw ValidationException::withMessages([
                    'importe' => 'Esta comanda ya esta pagada.',
                ]);
            }

            $metodo = $datos['metodo'];
            $importe = round((float) ($datos['importe'] ?? $pendiente), 2);

            if ($importe - $pendiente > 0.005) {
                throw ValidationException::withMessages([
                    'importe' => 'El importe cobrado no puede superar el pendiente.',
                ]);
            }

            $recibido = $metodo === MetodoPagoComanda::Efectivo
                ? round((float) ($datos['recibido'] ?? $importe), 2)
                : $importe;

            if ($metodo === MetodoPagoComanda::Efectivo && $recibido + 0.005 < $importe) {
                throw ValidationException::withMessages([
                    'recibido' => 'El efectivo recibido no puede ser menor que el importe cobrado.',
                ]);
            }

            $pago = $comanda->pagos()->create([
                'caja_turno_id' => $this->turnoAbiertoId($comanda),
                'metodo' => $metodo,
                'importe' => $importe,
                'recibido' => $recibido,
                'cambio' => max(0, round($recibido - $importe, 2)),
                'referencia' => $datos['referencia'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'cobrado_por' => $usuarioId,
                'cobrado_at' => now(),
            ]);

            $comanda->load('pagos');

            if ($comanda->pendientePago() <= 0.005) {
                $comanda->update([
                    'estado' => EstadoComanda::Pagada,
                    'cerrada_at' => now(),
                    'actualizado_por' => $usuarioId,
                ]);
            }

            return $pago;
        });
    }

    /**
     * Localiza una caja abierta compatible con la comanda.
     *
     * En esta primera fase no se bloquea el cobro si no hay caja abierta. Esto
     * evita romper la operativa actual y permite activar caja de forma gradual.
     */
    private function turnoAbiertoId(Comanda $comanda): ?string
    {
        return TurnoCaja::query()
            ->where('estado', EstadoTurnoCaja::Abierta)
            ->when(
                $comanda->recinto_id,
                fn ($query) => $query->where(function ($consulta) use ($comanda): void {
                    $consulta->where('recinto_id', $comanda->recinto_id)
                        ->orWhereNull('recinto_id');
                }),
            )
            ->latest('abierta_at')
            ->value('id');
    }
}
