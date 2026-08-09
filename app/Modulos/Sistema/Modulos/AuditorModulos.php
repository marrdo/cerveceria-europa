<?php

namespace App\Modulos\Sistema\Modulos;

use App\Models\Modulo;
use Illuminate\Support\Collection;

/**
 * Detecta divergencias entre catálogo, base de datos y dependencias activas.
 */
final readonly class AuditorModulos
{
    public function __construct(
        private CatalogoModulos $catalogo,
        private GestorModulos $modulos,
    ) {}

    /** @return Collection<int, string> */
    public function errores(): Collection
    {
        $errores = collect();
        $definiciones = $this->catalogo->todos();
        $clavesPersistidas = Modulo::query()->pluck('clave');

        foreach ($definiciones as $definicion) {
            foreach ([...$definicion->dependencias, ...$definicion->integraciones] as $relacionada) {
                if (! $definiciones->has($relacionada)) {
                    $errores->push("{$definicion->clave} referencia el módulo inexistente {$relacionada}.");
                }
            }

            if (in_array($definicion->clave, $this->catalogo->dependenciasTransitivas($definicion->clave), true)) {
                $errores->push("{$definicion->clave} contiene una dependencia circular.");
            }

            if (! $clavesPersistidas->contains($definicion->clave)) {
                $errores->push("Falta sincronizar {$definicion->clave} en la tabla modulos.");

                continue;
            }

            $activo = Modulo::query()
                ->where('clave', $definicion->clave)
                ->where('activo', true)
                ->exists();

            if ($activo && ! $this->modulos->estaOperativo($definicion->clave)) {
                $errores->push("{$definicion->clave} está activo pero alguna dependencia obligatoria no lo está.");
            }
        }

        $clavesPersistidas
            ->reject(fn (string $clave): bool => $definiciones->has($clave))
            ->each(fn (string $clave) => $errores->push("La tabla modulos contiene la clave no registrada {$clave}."));

        return $errores->values();
    }
}
