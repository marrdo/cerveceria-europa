<?php

namespace App\Modulos\PlanificacionTurnos\Http\Controllers\Empleado;

use App\Http\Controllers\Controller;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Muestra a cada persona únicamente sus turnos pertenecientes a cuadrantes publicados.
 */
final class MisTurnosController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usuario = $request->user();

        $cuadrantes = CuadranteLaboral::query()
            ->where('estado', EstadoCuadranteLaboral::Publicado)
            ->whereHas('jornadas', fn ($query) => $query->where('usuario_id', $usuario->id))
            ->orderByDesc('semana_inicio')
            ->get(['id', 'semana_inicio', 'estado', 'publicado_at']);

        $semanaSolicitada = $request->string('semana')->toString();
        $cuadrante = $cuadrantes->first(
            fn (CuadranteLaboral $item): bool => $item->semana_inicio->toDateString() === $semanaSolicitada,
        ) ?? $cuadrantes->first();

        $dias = collect();
        $incidenciasPorDia = collect();

        if ($cuadrante !== null) {
            $cuadrante->load([
                'jornadas' => fn ($query) => $query
                    ->where('usuario_id', $usuario->id)
                    ->with('areaTrabajo')
                    ->orderBy('fecha')
                    ->orderBy('hora_inicio'),
            ]);

            $dias = $this->diasSemana($cuadrante);
            $incidencias = IncidenciaLaboral::query()
                ->where(function ($query) use ($usuario): void {
                    $query->where('usuario_id', $usuario->id)->orWhereNull('usuario_id');
                })
                ->coincideConPeriodo($cuadrante->semana_inicio, $cuadrante->semanaFin())
                ->orderBy('fecha_inicio')
                ->get();

            $incidenciasPorDia = $dias->mapWithKeys(fn (Carbon $dia): array => [
                $dia->toDateString() => $incidencias
                    ->filter(fn (IncidenciaLaboral $incidencia): bool => $incidencia->afectaFecha($dia))
                    ->values(),
            ]);
        }

        return view('modulos.planificacion-turnos.empleado.mis-turnos', [
            'cuadrantes' => $cuadrantes,
            'cuadrante' => $cuadrante,
            'dias' => $dias,
            'incidenciasPorDia' => $incidenciasPorDia,
        ]);
    }

    /** @return Collection<int, Carbon> */
    private function diasSemana(CuadranteLaboral $cuadrante): Collection
    {
        return collect(range(0, 6))->map(
            fn (int $desplazamiento): Carbon => $cuadrante->semana_inicio->copy()->addDays($desplazamiento),
        );
    }
}
