<?php

namespace App\Modulos\PlanificacionTurnos\Enums;

/**
 * Situaciones de calendario que afectan a la disponibilidad laboral.
 */
enum TipoIncidenciaLaboral: string
{
    case Descanso = 'descanso';
    case Vacaciones = 'vacaciones';
    case Baja = 'baja';
    case Ausencia = 'ausencia';
    case Festivo = 'festivo';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Descanso => 'Descanso',
            self::Vacaciones => 'Vacaciones',
            self::Baja => 'Baja',
            self::Ausencia => 'Ausencia',
            self::Festivo => 'Festivo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Descanso => '#64748B',
            self::Vacaciones => '#0284C7',
            self::Baja => '#DC2626',
            self::Ausencia => '#D97706',
            self::Festivo => '#7C3AED',
        };
    }

    /**
     * Los festivos describen el calendario del negocio, no a una persona.
     */
    public function esGlobal(): bool
    {
        return $this === self::Festivo;
    }

    /**
     * Una incidencia personal impide asignar trabajo durante su vigencia.
     */
    public function bloqueaTrabajo(): bool
    {
        return ! $this->esGlobal();
    }
}
