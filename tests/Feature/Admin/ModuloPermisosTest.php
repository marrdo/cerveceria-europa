<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
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
}
