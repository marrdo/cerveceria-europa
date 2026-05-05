<?php

namespace App\Modulos\Ventas\Actions;

use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgregarLineasComandaAction
{
    /**
     * Agrega nuevas lineas a una comanda operativa desde contenidos de carta.
     *
     * @param array<string, mixed> $datos
     */
    public function execute(Comanda $comanda, array $datos, string $usuarioId): Comanda
    {
        return DB::transaction(function () use ($comanda, $datos, $usuarioId): Comanda {
            $comanda = Comanda::query()->lockForUpdate()->findOrFail($comanda->id);

            if (! $comanda->puedeRecibirLineas()) {
                throw ValidationException::withMessages([
                    'estado' => 'Solo puedes anadir productos a comandas abiertas, en preparacion o servidas pendientes de cobro.',
                ]);
            }

            $lineas = collect($datos['lineas'] ?? [])
                ->filter(fn (array $linea): bool => (float) ($linea['cantidad'] ?? 0) > 0)
                ->values();

            if ($lineas->isEmpty()) {
                throw ValidationException::withMessages([
                    'lineas' => 'Anade al menos un producto nuevo a la comanda.',
                ]);
            }

            $orden = (int) $comanda->lineas()->max('orden');

            foreach ($lineas as $linea) {
                $contenido = ContenidoWeb::query()
                    ->with(['producto', 'tarifas'])
                    ->findOrFail($linea['contenido_web_id']);

                $tarifa = $contenido->tarifas->firstWhere('id', $linea['tarifa_contenido_web_id'] ?? null)
                    ?? $contenido->tarifas->sortBy('orden')->first();

                $precio = round((float) ($tarifa?->precio ?? $contenido->precio ?? 0), 2);
                $cantidad = round((float) $linea['cantidad'], 3);
                $subtotal = round($cantidad * $precio, 2);

                $comanda->lineas()->create([
                    'contenido_web_id' => $contenido->id,
                    'producto_id' => $contenido->producto_id,
                    'nombre' => $tarifa && filled($tarifa->nombre)
                        ? "{$contenido->titulo} ({$tarifa->nombre})"
                        : $contenido->titulo,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                    'impuestos' => 0,
                    'total' => $subtotal,
                    'estado' => EstadoLineaComanda::Pendiente,
                    'notas' => $linea['notas'] ?? null,
                    'orden' => ++$orden,
                ]);
            }

            $comanda->recalcularTotales();
            $comanda->update([
                'estado' => EstadoComanda::EnPreparacion,
                'servida_at' => null,
                'actualizado_por' => $usuarioId,
            ]);

            return $comanda->refresh()->load('lineas');
        });
    }
}
