<?php

namespace Tests\Feature\Admin;

use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use Database\Seeders\AreaTrabajoSeeder;
use Database\Seeders\PersonalDemoSeeder;
use Database\Seeders\PlanificacionTurnosDemoSeeder;
use Database\Seeders\UsuarioRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanificacionTurnosDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_schedule_covers_the_week_for_all_22_operational_users(): void
    {
        $this->seed(UsuarioRolesSeeder::class);
        $this->seed(PersonalDemoSeeder::class);
        $this->seed(AreaTrabajoSeeder::class);
        $this->seed(PlanificacionTurnosDemoSeeder::class);

        $cuadrante = CuadranteLaboral::query()->sole();
        $jornadas = $cuadrante->jornadas()->get();

        $this->assertSame(22, $jornadas->pluck('usuario_id')->unique()->count());
        $this->assertGreaterThan(110, $jornadas->count());
        $this->assertSame(48, IncidenciaLaboral::query()->count());
        $this->assertDatabaseHas('incidencias_laborales', ['tipo' => 'descanso']);
        $this->assertDatabaseHas('incidencias_laborales', ['tipo' => 'vacaciones']);
        $this->assertDatabaseHas('incidencias_laborales', ['tipo' => 'baja']);
        $this->assertDatabaseHas('incidencias_laborales', ['tipo' => 'ausencia']);
        $this->assertDatabaseHas('incidencias_laborales', ['tipo' => 'festivo', 'usuario_id' => null]);

        $usuariosPorArea = $jornadas
            ->groupBy('area_trabajo_id')
            ->map(static fn ($tramos): int => $tramos->pluck('usuario_id')->unique()->count());

        $this->assertSame(17, $usuariosPorArea[AreaTrabajo::query()->where('nombre', 'Sala y barra')->value('id')]);
        $this->assertSame(5, $usuariosPorArea[AreaTrabajo::query()->where('nombre', 'Trastienda')->value('id')]);
    }
}
