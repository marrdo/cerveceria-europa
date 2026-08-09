<?php

namespace App\Modulos\PlanificacionTurnos\ViewData;

use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Prepara la matriz semanal que consume la interfaz del cuadrante.
 *
 * Mantener esta transformación fuera de Blade evita repetir filtros por cada
 * celda y deja la plantilla dedicada exclusivamente a presentar los datos.
 */
class CuadranteSemanalViewData
{
    /**
     * Construye una fila por empleado, incluyendo personas todavía sin turno.
     *
     * @param  Collection<int, Usuario>  $empleados
     * @param  Collection<int, Carbon>  $dias
     * @return Collection<int, array{
     *     empleado: Usuario,
     *     busqueda: string,
     *     area_ids: string,
     *     areas: Collection<int, mixed>,
     *     minutos: int,
     *     jornadas_por_dia: Collection<string, Collection<int, mixed>>
     * }>
     */
    public function construir(
        CuadranteLaboral $cuadrante,
        Collection $empleados,
        Collection $dias,
    ): Collection {
        $jornadasPorEmpleado = $cuadrante->jornadas->groupBy('usuario_id');

        return $empleados->map(function (Usuario $empleado) use ($dias, $jornadasPorEmpleado): array {
            $jornadas = $jornadasPorEmpleado->get($empleado->id, collect());
            $areas = $jornadas
                ->pluck('areaTrabajo')
                ->filter()
                ->unique('id')
                ->values();

            return [
                'empleado' => $empleado,
                'busqueda' => Str::lower(Str::ascii($empleado->nombre.' '.$empleado->rol->etiqueta())),
                'area_ids' => $areas->pluck('id')->implode(','),
                'areas' => $areas,
                'minutos' => $jornadas->sum(fn ($jornada): int => $jornada->minutosEfectivos()),
                'jornadas_por_dia' => $dias->mapWithKeys(
                    fn (Carbon $dia): array => [
                        $dia->toDateString() => $jornadas
                            ->filter(fn ($jornada): bool => $jornada->fecha->isSameDay($dia))
                            ->values(),
                    ],
                ),
            ];
        });
    }
}
