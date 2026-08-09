<?php

namespace Tests\Feature\Admin;

use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use Database\Seeders\AreaTrabajoSeeder;
use Database\Seeders\PersonalDemoSeeder;
use Database\Seeders\PlanificacionTurnosDemoSeeder;
use Database\Seeders\UsuarioRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanificacionTurnosDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_schedule_assigns_five_days_to_all_22_operational_users(): void
    {
        $this->seed(UsuarioRolesSeeder::class);
        $this->seed(PersonalDemoSeeder::class);
        $this->seed(AreaTrabajoSeeder::class);
        $this->seed(PlanificacionTurnosDemoSeeder::class);

        $cuadrante = CuadranteLaboral::query()->sole();
        $jornadas = $cuadrante->jornadas()->get();

        $this->assertSame(22, $jornadas->pluck('usuario_id')->unique()->count());
        $this->assertTrue(
            $jornadas->groupBy('usuario_id')->every(
                static fn ($tramos): bool => $tramos->pluck('fecha')->unique()->count() === 5,
            ),
        );
        $this->assertGreaterThan(110, $jornadas->count());

        $usuariosPorArea = $jornadas
            ->groupBy('area_trabajo_id')
            ->map(static fn ($tramos): int => $tramos->pluck('usuario_id')->unique()->count());

        $this->assertSame(17, $usuariosPorArea[AreaTrabajo::query()->where('nombre', 'Sala y barra')->value('id')]);
        $this->assertSame(5, $usuariosPorArea[AreaTrabajo::query()->where('nombre', 'Trastienda')->value('id')]);
    }
}
