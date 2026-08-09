<?php

namespace Database\Seeders;

use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use Illuminate\Database\Seeder;

/**
 * Crea areas laborales iniciales reutilizables en hosteleria.
 */
class AreaTrabajoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nombre' => 'Sala y barra', 'color' => '#2563EB', 'orden' => 10],
            ['nombre' => 'Trastienda', 'color' => '#7C3AED', 'orden' => 20],
        ] as $area) {
            AreaTrabajo::query()->updateOrCreate(
                ['nombre' => $area['nombre']],
                [...$area, 'activo' => true],
            );
        }
    }
}
