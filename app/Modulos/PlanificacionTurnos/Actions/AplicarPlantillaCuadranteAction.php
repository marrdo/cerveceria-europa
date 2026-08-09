<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\PlantillaCuadranteLaboral;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AplicarPlantillaCuadranteAction
{
    public function __construct(private readonly CrearJornadaLaboralAction $crearJornada) {}

    public function ejecutar(PlantillaCuadranteLaboral $plantilla, Carbon $semanaDestino): CuadranteLaboral
    {
        $lunes = $semanaDestino->copy()->startOfWeek();

        if (CuadranteLaboral::query()->whereDate('semana_inicio', $lunes->toDateString())->exists()) {
            throw ValidationException::withMessages(['semana_inicio' => 'Ya existe un cuadrante para la semana de destino.']);
        }

        return DB::transaction(function () use ($lunes, $plantilla): CuadranteLaboral {
            $cuadrante = CuadranteLaboral::query()->create([
                'semana_inicio' => $lunes,
                'estado' => EstadoCuadranteLaboral::Borrador,
                'notas' => 'Creado desde la plantilla '.$plantilla->nombre.'.',
            ]);

            foreach ($plantilla->jornadas()->get() as $jornada) {
                $this->crearJornada->ejecutar($cuadrante, [
                    'usuario_id' => $jornada->usuario_id,
                    'area_trabajo_id' => $jornada->area_trabajo_id,
                    'fecha' => $lunes->copy()->addDays($jornada->dia_semana - 1)->toDateString(),
                    'hora_inicio' => $jornada->hora_inicio,
                    'hora_fin' => $jornada->hora_fin,
                    'termina_dia_siguiente' => $jornada->termina_dia_siguiente,
                    'minutos_descanso' => $jornada->minutos_descanso,
                    'notas' => $jornada->notas,
                ]);
            }

            return $cuadrante;
        });
    }
}
