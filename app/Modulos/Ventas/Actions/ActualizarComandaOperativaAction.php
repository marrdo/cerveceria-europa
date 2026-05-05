<?php

namespace App\Modulos\Ventas\Actions;

use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\Ventas\Models\LineaComanda;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActualizarComandaOperativaAction
{
    /**
     * Actualiza datos operativos y lineas no servidas de una comanda.
     *
     * @param array<string, mixed> $datos
     */
    public function execute(Comanda $comanda, array $datos, string $usuarioId): Comanda
    {
        return DB::transaction(function () use ($comanda, $datos, $usuarioId): Comanda {
            $comanda = Comanda::query()->with('lineas')->lockForUpdate()->findOrFail($comanda->id);

            if (! $comanda->puedeEditarOperativa()) {
                throw ValidationException::withMessages([
                    'estado' => 'No puedes editar una comanda pagada o cancelada.',
                ]);
            }

            $comanda->fill([
                'mesa' => $datos['mesa'] ?? null,
                'cliente_nombre' => $datos['cliente_nombre'] ?? null,
                'ubicacion_inventario_id' => $datos['ubicacion_inventario_id'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'actualizado_por' => $usuarioId,
            ]);
            $comanda->save();

            foreach (($datos['lineas'] ?? []) as $lineaId => $datosLinea) {
                /** @var LineaComanda|null $linea */
                $linea = $comanda->lineas->firstWhere('id', $lineaId);

                if (! $linea) {
                    continue;
                }

                if ($linea->estaServida()) {
                    continue;
                }

                if ($linea->estado === EstadoLineaComanda::Cancelada) {
                    continue;
                }

                if (($datosLinea['cancelar'] ?? false) === true) {
                    $linea->update([
                        'estado' => EstadoLineaComanda::Cancelada,
                        'notas' => $datosLinea['notas'] ?? $linea->notas,
                    ]);

                    continue;
                }

                $cantidad = round((float) ($datosLinea['cantidad'] ?? $linea->cantidad), 3);

                if ($cantidad <= 0) {
                    $linea->update([
                        'estado' => EstadoLineaComanda::Cancelada,
                        'notas' => $datosLinea['notas'] ?? $linea->notas,
                    ]);

                    continue;
                }

                $subtotal = round($cantidad * (float) $linea->precio_unitario, 2);

                $linea->update([
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                    'impuestos' => 0,
                    'total' => $subtotal,
                    'notas' => $datosLinea['notas'] ?? null,
                ]);
            }

            $comanda->recalcularTotales();
            $this->actualizarEstado($comanda, $usuarioId);

            return $comanda->refresh()->load('lineas');
        });
    }

    /**
     * Recalcula el estado general tras editar o cancelar lineas.
     */
    private function actualizarEstado(Comanda $comanda, string $usuarioId): void
    {
        $lineasActivas = $comanda->lineas()
            ->where('estado', '!=', EstadoLineaComanda::Cancelada->value)
            ->get();

        if ($lineasActivas->isEmpty()) {
            $comanda->update([
                'estado' => EstadoComanda::Cancelada,
                'cerrada_at' => now(),
                'actualizado_por' => $usuarioId,
            ]);

            return;
        }

        $todasServidas = $lineasActivas->every(fn (LineaComanda $linea): bool => $linea->estado === EstadoLineaComanda::Servida);
        $tieneServidas = $lineasActivas->contains(fn (LineaComanda $linea): bool => $linea->estado === EstadoLineaComanda::Servida);

        $comanda->update([
            'estado' => $todasServidas
                ? EstadoComanda::Servida
                : ($tieneServidas ? EstadoComanda::EnPreparacion : EstadoComanda::Abierta),
            'servida_at' => $todasServidas ? ($comanda->servida_at ?? now()) : null,
            'actualizado_por' => $usuarioId,
        ]);
    }
}
