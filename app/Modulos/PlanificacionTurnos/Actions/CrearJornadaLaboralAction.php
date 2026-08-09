<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\JornadaLaboral;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Crea un tramo laboral después de comprobar duración y solapamientos.
 */
class CrearJornadaLaboralAction
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(CuadranteLaboral $cuadrante, array $datos): JornadaLaboral
    {
        $inicio = Carbon::parse($datos['fecha'].' '.$datos['hora_inicio']);
        $fin = Carbon::parse($datos['fecha'].' '.$datos['hora_fin']);

        if ($datos['termina_dia_siguiente']) {
            $fin->addDay();
        }

        if ($fin->lessThanOrEqualTo($inicio)) {
            throw ValidationException::withMessages([
                'hora_fin' => 'La hora de fin debe ser posterior al inicio o debes marcar que termina al día siguiente.',
            ]);
        }

        $duracion = (int) $inicio->diffInMinutes($fin);

        if ((int) $datos['minutos_descanso'] >= $duracion) {
            throw ValidationException::withMessages([
                'minutos_descanso' => 'El descanso debe ser menor que la duración total del tramo.',
            ]);
        }

        $solapada = JornadaLaboral::query()
            ->where('usuario_id', $datos['usuario_id'])
            ->whereDate('fecha', '>=', $inicio->copy()->subDay()->toDateString())
            ->whereDate('fecha', '<=', $fin->toDateString())
            ->get()
            ->contains(fn (JornadaLaboral $jornada): bool => $inicio->lt($jornada->fin()) && $fin->gt($jornada->inicio()));

        if ($solapada) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'Este tramo se solapa con otra jornada del empleado.',
            ]);
        }

        $incidencia = IncidenciaLaboral::query()
            ->where('usuario_id', $datos['usuario_id'])
            ->coincideConPeriodo($inicio->copy()->startOfDay(), $fin->copy()->endOfDay())
            ->get()
            ->first(function (IncidenciaLaboral $incidencia) use ($inicio, $fin): bool {
                $inicioIncidencia = $incidencia->fecha_inicio->copy()->startOfDay();
                $finIncidencia = $incidencia->fecha_fin->copy()->addDay()->startOfDay();

                return $inicio->lt($finIncidencia) && $fin->gt($inicioIncidencia);
            });

        if ($incidencia !== null) {
            throw ValidationException::withMessages([
                'fecha' => sprintf(
                    'No puedes asignar trabajo: el empleado tiene %s en ese periodo.',
                    mb_strtolower($incidencia->tipo->etiqueta()),
                ),
            ]);
        }

        return $cuadrante->jornadas()->create($datos);
    }
}
