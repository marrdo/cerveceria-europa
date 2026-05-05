<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use Database\Seeders\InventarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuloPermisosTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiter_cannot_access_inventory_or_purchase_modules(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.productos.index'))
            ->assertForbidden();

        $this->actingAs($usuario)
            ->get(route('admin.compras.pedidos.index'))
            ->assertForbidden();
    }

    public function test_manager_can_access_inventory_and_purchase_modules(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.productos.index'))
            ->assertOk();

        $this->actingAs($usuario)
            ->get(route('admin.compras.pedidos.index'))
            ->assertOk();
    }

    public function test_waiter_manager_and_owner_can_access_sales_when_module_is_active(): void
    {
        Modulo::query()->create([
            'clave' => 'ventas',
            'nombre' => 'Ventas',
            'activo' => true,
        ]);

        foreach ([RolUsuario::Camarero, RolUsuario::Encargado, RolUsuario::Propietario] as $rol) {
            $usuario = Usuario::factory()->create(['rol' => $rol]);

            $this->actingAs($usuario)
                ->get(route('admin.ventas.comandas.index'))
                ->assertOk();
        }
    }

    public function test_waiter_cannot_access_sales_when_module_is_disabled(): void
    {
        Modulo::query()->create([
            'clave' => 'ventas',
            'nombre' => 'Ventas',
            'activo' => false,
        ]);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);

        $this->actingAs($usuario)
            ->get(route('admin.ventas.comandas.index'))
            ->assertForbidden();
    }

    public function test_sidebar_groups_available_modules_for_manager(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Inventario')
            ->assertSee('Compras a proveedor')
            ->assertSee('Productos')
            ->assertSee('Pedidos');
    }

    public function test_owner_only_sees_public_web_module_when_it_is_active(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);

        Modulo::query()->create([
            'clave' => 'web_publica',
            'nombre' => 'Modulo Web Publica',
            'activo' => false,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Web publica');

        Modulo::query()->where('clave', 'web_publica')->update(['activo' => true]);

        $this->actingAs($usuario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Web publica');
    }

    public function test_superadmin_sees_contract_module_controls_on_dashboard(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Superadmin]);

        Modulo::query()->create([
            'clave' => 'web_publica',
            'nombre' => 'Modulo Web Publica',
            'descripcion' => 'Web gestionable.',
            'activo' => true,
        ]);
        Modulo::query()->create([
            'clave' => 'blog',
            'nombre' => 'Modulo Blog',
            'descripcion' => 'Blog opcional.',
            'activo' => false,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Modulos contratados')
            ->assertSee('Modulo Web Publica')
            ->assertSee('Modulo Blog')
            ->assertSee('Desactivar')
            ->assertSee('Activar');
    }

    public function test_owner_does_not_see_contract_module_controls_on_dashboard(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);

        Modulo::query()->create([
            'clave' => 'web_publica',
            'nombre' => 'Modulo Web Publica',
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Modulos contratados');
    }

    public function test_superadmin_can_toggle_contract_module_from_dashboard_controls(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Superadmin]);
        $modulo = Modulo::query()->create([
            'clave' => 'web_publica',
            'nombre' => 'Modulo Web Publica',
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->patch(route('admin.modulos.toggle', $modulo))
            ->assertRedirect();

        $this->assertFalse($modulo->fresh()->activo);
    }
}
