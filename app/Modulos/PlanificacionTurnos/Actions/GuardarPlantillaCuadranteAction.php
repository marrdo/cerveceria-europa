<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\PlantillaCuadranteLaboral;
use Illuminate\Support\Facades\DB;

class GuardarPlantillaCuadranteAction
{
    /** @param array<string, mixed> $datos */
    public function ejecutar(CuadranteLaboral $cuadrante, array $datos): PlantillaCuadranteLaboral
    {
        return DB::transaction(function () use ($cuadrante, $datos): PlantillaCuadranteLaboral {
            $plantilla = PlantillaCuadranteLaboral::query()->create($datos);

            foreach ($cuadrante->jornadas()->get() as $jornada) {
                $plantilla->jornadas()->create([
                    'usuario_id' => $jornada->usuario_id,
                    'area_trabajo_id' => $jornada->area_trabajo_id,
                    'dia_semana' => $jornada->fecha->dayOfWeekIso,
                    'hora_inicio' => $jornada->hora_inicio,
                    'hora_fin' => $jornada->hora_fin,
                    'termina_dia_siguiente' => $jornada->termina_dia_siguiente,
                    'minutos_descanso' => $jornada->minutos_descanso,
                    'notas' => $jornada->notas,
                ]);
            }

            return $plantilla;
        });
    }
}
