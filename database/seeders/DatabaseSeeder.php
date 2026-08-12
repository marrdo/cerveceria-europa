<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UsuarioAdministradorSeeder::class);
        $this->call(UsuarioRolesSeeder::class);
        $this->call(PersonalDemoSeeder::class);
        $this->call(ModuloSeeder::class);
        $this->call(ConfiguracionNegocioSeeder::class);
        $this->call(AreaTrabajoSeeder::class);
        $this->call(PlanificacionTurnosDemoSeeder::class);
        $this->call(PlanificacionProductividadDemoSeeder::class);
        $this->call(RecorridoDemoSeeder::class);
    }
}
