<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Database\Seeders\PersonalDemoSeeder;
use Database\Seeders\UsuarioAdministradorSeeder;
use Database\Seeders\UsuarioRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioRolesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeders_create_22_operational_profiles_and_the_superadmin(): void
    {
        $this->seed(UsuarioAdministradorSeeder::class);
        $this->seed(UsuarioRolesSeeder::class);
        $this->seed(PersonalDemoSeeder::class);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'superadmin@demo.local',
            'rol' => RolUsuario::Superadmin->value,
            'es_protegido' => true,
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'camarero@demo.local',
            'rol' => RolUsuario::Camarero->value,
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'encargado@demo.local',
            'rol' => RolUsuario::Encargado->value,
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'propietario@demo.local',
            'rol' => RolUsuario::Propietario->value,
        ]);

        $this->assertSame(23, Usuario::query()->count());
        $this->assertSame(22, Usuario::query()->where('rol', '!=', RolUsuario::Superadmin)->count());
        $this->assertSame(19, Usuario::query()->where('rol', RolUsuario::Camarero)->count());
        $this->assertSame(2, Usuario::query()->where('rol', RolUsuario::Encargado)->count());
        $this->assertSame(1, Usuario::query()->where('rol', RolUsuario::Propietario)->count());
    }

    public function test_generated_personal_seeder_is_idempotent(): void
    {
        $this->seed(UsuarioRolesSeeder::class);
        $this->seed(PersonalDemoSeeder::class);
        $this->seed(PersonalDemoSeeder::class);

        $this->assertSame(22, Usuario::query()->count());
        $this->assertSame(19, Usuario::query()->where('email', 'like', 'equipo%@demo.local')->count());
    }
}
