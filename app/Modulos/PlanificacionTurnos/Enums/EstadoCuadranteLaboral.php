<?php

namespace App\Modulos\PlanificacionTurnos\Enums;

/**
 * Estado editorial de un cuadrante laboral semanal.
 */
enum EstadoCuadranteLaboral: string
{
    case Borrador = 'borrador';
    case Publicado = 'publicado';

    /**
     * Etiqueta legible para el panel de administracion.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Publicado => 'Publicado',
        };
    }
}
