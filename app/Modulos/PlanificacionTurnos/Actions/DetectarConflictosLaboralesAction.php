<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\JornadaLaboral;
use Illuminate\Support\Collection;

/**
 * Detecta trabajo asignado durante una incidencia que bloquea disponibilidad.
 */
class DetectarConflictosLaboralesAction
{
    /**
     * @return Collection<int, JornadaLaboral>
     */
    public function ejecutar(CuadranteLaboral $cuadrante): Collection
    {
        $jornadas = $cuadrante->relationLoaded('jornadas')
            ? $cuadrante->jornadas
            : $cuadrante->jornadas()->get();
        $usuarios = $jornadas->pluck('usuario_id')->unique()->values();

        if ($usuarios->isEmpty()) {
            return collect();
        }

        $incidencias = IncidenciaLaboral::query()
            ->whereIn('usuario_id', $usuarios)
            ->coincideConPeriodo($cuadrante->semana_inicio, $cuadrante->semanaFin())
            ->get()
            ->groupBy('usuario_id');

        return $jornadas
            ->filter(function (JornadaLaboral $jornada) use ($incidencias): bool {
                return $incidencias
                    ->get($jornada->usuario_id, collect())
                    ->contains(fn (IncidenciaLaboral $incidencia): bool => $incidencia->afectaFecha($jornada->fecha));
            })
            ->values();
    }
}
