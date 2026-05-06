<?php

namespace App\Modulos\Ventas\Actions;

use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use App\Modulos\Ventas\Models\PagoComanda;
use App\Modulos\Ventas\Models\TurnoCaja;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CerrarTurnoCajaAction
{
    /**
     * Cierra caja calculando ventas por metodo, efectivo esperado y descuadre.
     *
     * @param array<string, mixed> $datos
     */
    public function execute(TurnoCaja $turnoCaja, array $datos, string $usuarioId): TurnoCaja
    {
        return DB::transaction(function () use ($turnoCaja, $datos, $usuarioId): TurnoCaja {
            $turnoCaja = TurnoCaja::query()->lockForUpdate()->findOrFail($turnoCaja->id);

            if (! $turnoCaja->estaAbierta()) {
                throw ValidationException::withMessages([
                    'estado' => 'Esta caja ya esta cerrada.',
                ]);
            }

            $resumen = $this->resumenPagos($turnoCaja);
            $efectivoEsperado = round((float) $turnoCaja->saldo_inicial + $resumen['efectivo'] - $resumen['cambio'], 2);
            $efectivoContado = $datos['efectivo_contado'];

            $turnoCaja->update([
                'estado' => EstadoTurnoCaja::Cerrada,
                'efectivo_esperado' => $efectivoEsperado,
                'efectivo_contado' => $efectivoContado,
                'descuadre' => round($efectivoContado - $efectivoEsperado, 2),
                'total_ventas' => $resumen['total'],
                'total_efectivo' => $resumen['efectivo'],
                'total_tarjeta' => $resumen['tarjeta'],
                'total_bizum' => $resumen['bizum'],
                'total_invitacion' => $resumen['invitacion'],
                'total_otro' => $resumen['otro'],
                'total_cambio' => $resumen['cambio'],
                'pagos_count' => $resumen['pagos_count'],
                'cerrada_por' => $usuarioId,
                'cerrada_at' => now(),
                'notas_cierre' => $datos['notas_cierre'] ?? null,
            ]);

            return $turnoCaja->refresh();
        });
    }

    /**
     * Calcula importes cobrados por metodo dentro del turno.
     *
     * @return array{total: float, efectivo: float, tarjeta: float, bizum: float, invitacion: float, otro: float, cambio: float, pagos_count: int}
     */
    private function resumenPagos(TurnoCaja $turnoCaja): array
    {
        $pagos = PagoComanda::query()
            ->where('caja_turno_id', $turnoCaja->id)
            ->get(['metodo', 'importe', 'cambio']);

        return [
            'total' => round((float) $pagos->sum('importe'), 2),
            'efectivo' => $this->sumarMetodo($pagos, MetodoPagoComanda::Efectivo),
            'tarjeta' => $this->sumarMetodo($pagos, MetodoPagoComanda::Tarjeta),
            'bizum' => $this->sumarMetodo($pagos, MetodoPagoComanda::Bizum),
            'invitacion' => $this->sumarMetodo($pagos, MetodoPagoComanda::Invitacion),
            'otro' => $this->sumarMetodo($pagos, MetodoPagoComanda::Otro),
            'cambio' => round((float) $pagos->sum('cambio'), 2),
            'pagos_count' => $pagos->count(),
        ];
    }

    /**
     * Suma importes de un metodo concreto.
     */
    private function sumarMetodo($pagos, MetodoPagoComanda $metodo): float
    {
        return round((float) $pagos
            ->filter(fn (PagoComanda $pago): bool => $pago->metodo === $metodo)
            ->sum('importe'), 2);
    }
}
