<?php

namespace App\Modulos\Ventas\Actions;

use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use App\Modulos\Ventas\Models\TurnoCaja;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbrirTurnoCajaAction
{
    /**
     * Abre un turno de caja si no existe otro abierto para el mismo recinto.
     *
     * @param array<string, mixed> $datos
     */
    public function execute(array $datos, string $usuarioId): TurnoCaja
    {
        return DB::transaction(function () use ($datos, $usuarioId): TurnoCaja {
            $recintoId = $datos['recinto_id'] ?? null;

            $existeTurnoAbierto = TurnoCaja::query()
                ->where('estado', EstadoTurnoCaja::Abierta)
                ->when(
                    $recintoId,
                    fn ($query) => $query->where('recinto_id', $recintoId),
                    fn ($query) => $query->whereNull('recinto_id'),
                )
                ->lockForUpdate()
                ->exists();

            if ($existeTurnoAbierto) {
                throw ValidationException::withMessages([
                    'recinto_id' => 'Ya existe una caja abierta para este recinto.',
                ]);
            }

            return TurnoCaja::query()->create([
                'numero' => $this->generarNumero(),
                'recinto_id' => $recintoId,
                'estado' => EstadoTurnoCaja::Abierta,
                'saldo_inicial' => $datos['saldo_inicial'],
                'efectivo_esperado' => $datos['saldo_inicial'],
                'abierta_por' => $usuarioId,
                'abierta_at' => now(),
                'notas_apertura' => $datos['notas_apertura'] ?? null,
            ]);
        });
    }

    /**
     * Genera una numeracion legible diaria para turnos de caja.
     */
    private function generarNumero(): string
    {
        $prefijo = 'CAJA-'.now()->format('Ymd');
        $contador = TurnoCaja::query()->where('numero', 'like', "{$prefijo}%")->count() + 1;

        return $prefijo.'-'.str_pad((string) $contador, 3, '0', STR_PAD_LEFT);
    }
}
