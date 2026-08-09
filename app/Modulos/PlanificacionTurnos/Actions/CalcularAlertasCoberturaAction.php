<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Modulos\PlanificacionTurnos\Models\CoberturaMinimaLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\JornadaLaboral;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Calcula tramos de 30 minutos donde no se alcanza la cobertura configurada.
 */
class CalcularAlertasCoberturaAction
{
    /**
     * @param  Collection<int, CoberturaMinimaLaboral>  $reglas
     * @return Collection<int, array{
     *     fecha: Carbon,
     *     area: mixed,
     *     hora_inicio: string,
     *     hora_fin: string,
     *     minimo: int,
     *     disponibles: int
     * }>
     */
    public function ejecutar(CuadranteLaboral $cuadrante, Collection $reglas): Collection
    {
        $jornadas = $cuadrante->relationLoaded('jornadas')
            ? $cuadrante->jornadas
            : $cuadrante->jornadas()->get();
        $alertas = collect();

        foreach ($reglas->where('activo', true) as $regla) {
            $fecha = $cuadrante->semana_inicio->copy()->addDays($regla->dia_semana - 1);
            $cursor = Carbon::parse($fecha->toDateString().' '.$regla->hora_inicio);
            $finRegla = Carbon::parse($fecha->toDateString().' '.$regla->hora_fin);
            $alertaActual = null;

            while ($cursor->lt($finRegla)) {
                $finTramo = $cursor->copy()->addMinutes(30)->min($finRegla);
                $disponibles = $jornadas
                    ->filter(fn (JornadaLaboral $jornada): bool => $jornada->area_trabajo_id === $regla->area_trabajo_id
                        && $cursor->lt($jornada->fin())
                        && $finTramo->gt($jornada->inicio()))
                    ->pluck('usuario_id')
                    ->unique()
                    ->count();

                if ($disponibles < $regla->minimo_personas) {
                    if ($alertaActual !== null && $alertaActual['disponibles'] === $disponibles) {
                        $alertaActual['hora_fin'] = $finTramo->format('H:i');
                    } else {
                        if ($alertaActual !== null) {
                            $alertas->push($alertaActual);
                        }

                        $alertaActual = [
                            'fecha' => $fecha,
                            'area' => $regla->areaTrabajo,
                            'hora_inicio' => $cursor->format('H:i'),
                            'hora_fin' => $finTramo->format('H:i'),
                            'minimo' => $regla->minimo_personas,
                            'disponibles' => $disponibles,
                        ];
                    }
                } elseif ($alertaActual !== null) {
                    $alertas->push($alertaActual);
                    $alertaActual = null;
                }

                $cursor = $finTramo;
            }

            if ($alertaActual !== null) {
                $alertas->push($alertaActual);
            }
        }

        return $alertas;
    }
}
