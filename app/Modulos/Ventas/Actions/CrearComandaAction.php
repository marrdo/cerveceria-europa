<?php

namespace App\Modulos\Ventas\Actions;

use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrearComandaAction
{
    /**
     * Crea una comanda con lineas desde contenidos de carta.
     *
     * @param array<string, mixed> $datos
     */
    public function execute(array $datos, string $usuarioId): Comanda
    {
        return DB::transaction(function () use ($datos, $usuarioId): Comanda {
            $lineas = collect($datos['lineas'] ?? [])
                ->filter(fn (array $linea): bool => (float) ($linea['cantidad'] ?? 0) > 0)
                ->values();

            if ($lineas->isEmpty()) {
                throw ValidationException::withMessages([
                    'lineas' => 'Anade al menos un producto a la comanda.',
                ]);
            }

            $comanda = Comanda::query()->create([
                'numero' => $this->generarNumero(),
                'mesa' => $datos['mesa'] ?? null,
                'cliente_nombre' => $datos['cliente_nombre'] ?? null,
                'ubicacion_inventario_id' => $datos['ubicacion_inventario_id'] ?? null,
                'estado' => EstadoComanda::Abierta,
                'notas' => $datos['notas'] ?? null,
                'creado_por' => $usuarioId,
                'actualizado_por' => $usuarioId,
            ]);

            foreach ($lineas as $indice => $linea) {
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
                    'orden' => $indice + 1,
                ]);
            }

            $comanda->recalcularTotales();

            return $comanda->refresh()->load('lineas');
        });
    }

    private function generarNumero(): string
    {
        $prefijo = 'COM-'.now()->format('Ymd').'-';
        $contador = Comanda::query()->where('numero', 'like', "{$prefijo}%")->count() + 1;

        return $prefijo.str_pad((string) $contador, 4, '0', STR_PAD_LEFT);
    }
}
