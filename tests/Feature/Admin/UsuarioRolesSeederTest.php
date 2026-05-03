<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Database\Seeders\UsuarioAdministradorSeeder;
use Database\Seeders\UsuarioRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioRolesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeders_create_one_profile_for_each_initial_role(): void
    {
        $this->seed(UsuarioAdministradorSeeder::class);
        $this->seed(UsuarioRolesSeeder::class);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'admin@cerveceria-europa.local',
            'rol' => RolUsuario::Superadmin->value,
            'es_protegido' => true,
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'camarero@cerveceria-europa.local',
            'rol' => RolUsuario::Camarero->value,
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'encargado@cerveceria-europa.local',
            'rol' => RolUsuario::Encargado->value,
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'propietario@cerveceria-europa.local',
            'rol' => RolUsuario::Propietario->value,
        ]);

        $this->assertSame(4, Usuario::query()->count());
    }
}
