<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Actions\GuardarPlantillaCuadranteAction;
use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CoberturaMinimaLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\PlantillaCuadranteLaboral;
use Illuminate\Database\Seeder;

/**
 * Añade contratos, cobertura y una plantilla reutilizable a la demo.
 */
class PlanificacionProductividadDemoSeeder extends Seeder
{
    public function run(GuardarPlantillaCuadranteAction $guardarPlantilla): void
    {
        $usuarios = Usuario::query()
            ->where('rol', '!=', RolUsuario::Superadmin)
            ->orderBy('email')
            ->get();

        foreach ($usuarios as $indice => $usuario) {
            $usuario->update([
                'minutos_contrato_semanales' => match ($indice % 5) {
                    0 => 1200,
                    1 => 1800,
                    default => 2400,
                },
            ]);
        }

        $areas = AreaTrabajo::query()->get()->keyBy('nombre');

        foreach (range(1, 7) as $diaSemana) {
            $this->guardarCobertura($areas['Sala y barra']->id, $diaSemana, '12:00', '16:00', 4);
            $this->guardarCobertura($areas['Trastienda']->id, $diaSemana, '09:00', '12:00', 2);
        }

        PlantillaCuadranteLaboral::query()->where('nombre', 'Semana base demo')->delete();
        $cuadrante = CuadranteLaboral::query()->orderByDesc('semana_inicio')->firstOrFail();

        $guardarPlantilla->ejecutar($cuadrante, [
            'nombre' => 'Semana base demo',
            'descripcion' => 'Patrón ficticio con el equipo y horarios habituales.',
            'creado_por_id' => null,
        ]);
    }

    private function guardarCobertura(
        string $areaId,
        int $diaSemana,
        string $inicio,
        string $fin,
        int $minimo,
    ): void {
        CoberturaMinimaLaboral::query()->updateOrCreate(
            [
                'area_trabajo_id' => $areaId,
                'dia_semana' => $diaSemana,
                'hora_inicio' => $inicio,
                'hora_fin' => $fin,
            ],
            ['minimo_personas' => $minimo, 'activo' => true],
        );
    }
}
