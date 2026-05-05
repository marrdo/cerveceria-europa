<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Modulos\Espacios\Models\Mesa;
use App\Modulos\Espacios\Models\Recinto;
use App\Modulos\Espacios\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspaciosModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_recinto_zone_and_table(): void
    {
        $this->activarModuloEspacios();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->post(route('admin.espacios.recintos.store'), [
                'nombre_comercial' => 'Cerveceria Europa',
                'localidad' => 'Sevilla',
                'activo' => 1,
            ])
            ->assertRedirect(route('admin.espacios.recintos.index'));

        $recinto = Recinto::query()->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.espacios.zonas.store'), [
                'recinto_id' => $recinto->id,
                'nombre' => 'Terraza 1',
                'orden' => 1,
                'activa' => 1,
            ])
            ->assertRedirect(route('admin.espacios.zonas.index'));

        $zona = Zona::query()->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.espacios.mesas.store'), [
                'zona_id' => $zona->id,
                'nombre' => 'Mesa 4',
                'capacidad' => 4,
                'activa' => 1,
            ])
            ->assertRedirect(route('admin.espacios.mesas.index'));

        $this->assertDatabaseHas('mesas', [
            'nombre' => 'Mesa 4',
            'zona_id' => $zona->id,
            'activa' => true,
        ]);
    }

    public function test_waiter_cannot_manage_spaces(): void
    {
        $this->activarModuloEspacios();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);

        $this->actingAs($usuario)
            ->get(route('admin.espacios.recintos.index'))
            ->assertForbidden();
    }

    public function test_disabled_spaces_module_blocks_manager_access(): void
    {
        Modulo::query()->create([
            'clave' => 'espacios',
            'nombre' => 'Espacios',
            'activo' => false,
        ]);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->get(route('admin.espacios.recintos.index'))
            ->assertForbidden();
    }

    private function activarModuloEspacios(): void
    {
        Modulo::query()->create([
            'clave' => 'espacios',
            'nombre' => 'Espacios',
            'activo' => true,
        ]);
    }
}
