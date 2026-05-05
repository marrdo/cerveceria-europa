<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_waiters_only(): void
    {
        $this->activarPersonal();
        $encargado = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($encargado)
            ->post(route('admin.personal.usuarios.store'), [
                'nombre' => 'Camarero Nuevo',
                'email' => 'camarero.nuevo@example.test',
                'rol' => RolUsuario::Camarero->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.personal.index'));

        $this->assertDatabaseHas('usuarios', [
            'email' => 'camarero.nuevo@example.test',
            'rol' => RolUsuario::Camarero->value,
        ]);

        $this->actingAs($encargado)
            ->from(route('admin.personal.usuarios.create'))
            ->post(route('admin.personal.usuarios.store'), [
                'nombre' => 'Encargado Ilegal',
                'email' => 'encargado.ilegal@example.test',
                'rol' => RolUsuario::Encargado->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.personal.usuarios.create'))
            ->assertSessionHasErrors('rol');
    }

    public function test_owner_can_create_waiters_and_managers(): void
    {
        $this->activarPersonal();
        $propietario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);

        foreach ([RolUsuario::Camarero, RolUsuario::Encargado] as $rol) {
            $this->actingAs($propietario)
                ->post(route('admin.personal.usuarios.store'), [
                    'nombre' => 'Usuario '.$rol->value,
                    'email' => $rol->value.'@example.test',
                    'rol' => $rol->value,
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ])
                ->assertRedirect(route('admin.personal.index'));
        }

        $this->assertDatabaseHas('usuarios', ['email' => 'camarero@example.test']);
        $this->assertDatabaseHas('usuarios', ['email' => 'encargado@example.test']);
    }

    public function test_superadmin_can_create_all_roles(): void
    {
        $this->activarPersonal();
        $superadmin = Usuario::factory()->create(['rol' => RolUsuario::Superadmin]);

        $this->actingAs($superadmin)
            ->post(route('admin.personal.usuarios.store'), [
                'nombre' => 'Propietario Nuevo',
                'email' => 'propietario.nuevo@example.test',
                'rol' => RolUsuario::Propietario->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.personal.index'));

        $this->assertDatabaseHas('usuarios', [
            'email' => 'propietario.nuevo@example.test',
            'rol' => RolUsuario::Propietario->value,
        ]);
    }

    public function test_owner_can_view_edit_and_delete_managed_users(): void
    {
        $this->activarPersonal();
        $propietario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        $camarero = Usuario::factory()->create([
            'nombre' => 'Camarero Inicial',
            'email' => 'camarero.inicial@example.test',
            'rol' => RolUsuario::Camarero,
        ]);

        $this->actingAs($propietario)
            ->get(route('admin.personal.usuarios.show', $camarero))
            ->assertOk()
            ->assertSee('Camarero Inicial')
            ->assertSee('Editar');

        $this->actingAs($propietario)
            ->put(route('admin.personal.usuarios.update', $camarero), [
                'nombre' => 'Camarero Editado',
                'email' => 'camarero.editado@example.test',
                'rol' => RolUsuario::Encargado->value,
            ])
            ->assertRedirect(route('admin.personal.usuarios.show', $camarero));

        $this->assertDatabaseHas('usuarios', [
            'id' => $camarero->id,
            'nombre' => 'Camarero Editado',
            'email' => 'camarero.editado@example.test',
            'rol' => RolUsuario::Encargado->value,
        ]);

        $this->actingAs($propietario)
            ->delete(route('admin.personal.usuarios.destroy', $camarero))
            ->assertRedirect(route('admin.personal.index'));

        $this->assertSoftDeleted('usuarios', [
            'id' => $camarero->id,
        ]);
    }

    public function test_manager_cannot_view_or_edit_manager_users(): void
    {
        $this->activarPersonal();
        $encargado = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $otroEncargado = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($encargado)
            ->get(route('admin.personal.usuarios.show', $otroEncargado))
            ->assertForbidden();

        $this->actingAs($encargado)
            ->put(route('admin.personal.usuarios.update', $otroEncargado), [
                'nombre' => 'Cambio no permitido',
                'email' => 'cambio.no.permitido@example.test',
                'rol' => RolUsuario::Encargado->value,
            ])
            ->assertForbidden();
    }

    public function test_waiter_cannot_access_staff_management(): void
    {
        $this->activarPersonal();
        $camarero = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);

        $this->actingAs($camarero)
            ->get(route('admin.personal.index'))
            ->assertForbidden();
    }

    private function activarPersonal(): void
    {
        Modulo::query()->create([
            'clave' => 'personal',
            'nombre' => 'Gestion de personal',
            'activo' => true,
        ]);
    }
}
