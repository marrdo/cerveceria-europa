<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Modulos\PlanificacionTurnos\Enums\TipoIncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Registra una incidencia después de descartar periodos incompatibles.
 */
class CrearIncidenciaLaboralAction
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(array $datos): IncidenciaLaboral
    {
        $tipo = $datos['tipo'] instanceof TipoIncidenciaLaboral
            ? $datos['tipo']
            : TipoIncidenciaLaboral::from((string) $datos['tipo']);
        $inicio = Carbon::parse((string) $datos['fecha_inicio']);
        $fin = Carbon::parse((string) $datos['fecha_fin']);

        $solapada = IncidenciaLaboral::query()
            ->when(
                $tipo->esGlobal(),
                fn ($query) => $query->whereNull('usuario_id')->where('tipo', TipoIncidenciaLaboral::Festivo),
                fn ($query) => $query->where('usuario_id', $datos['usuario_id'])->where('tipo', '!=', TipoIncidenciaLaboral::Festivo),
            )
            ->coincideConPeriodo($inicio, $fin)
            ->exists();

        if ($solapada) {
            throw ValidationException::withMessages([
                'fecha_inicio' => $tipo->esGlobal()
                    ? 'Ya existe un festivo que coincide con este periodo.'
                    : 'El empleado ya tiene otra incidencia que coincide con este periodo.',
            ]);
        }

        return IncidenciaLaboral::query()->create([
            ...$datos,
            'tipo' => $tipo,
        ]);
    }
}
