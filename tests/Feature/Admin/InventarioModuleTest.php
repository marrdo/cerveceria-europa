<?php

namespace Tests\Feature\Admin;

use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use App\Models\Usuario;
use Database\Seeders\InventarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_product_list_can_be_rendered(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)
            ->get(route('admin.inventario.productos.index'))
            ->assertOk()
            ->assertSee('Productos');
    }

    public function test_product_can_be_created(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $categoria = CategoriaProducto::query()->firstOrFail();
        $unidad = UnidadInventario::query()->where('codigo', 'ud')->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.inventario.productos.store'), [
                'categoria_producto_id' => $categoria->id,
                'unidad_inventario_id' => $unidad->id,
                'nombre' => 'Cerveza de prueba',
                'sku' => 'CE-PRUEBA',
                'precio_venta' => 2.50,
                'precio_coste' => 1.10,
                'cantidad_alerta_stock' => 12,
                'controla_stock' => '1',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.inventario.productos.index'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Cerveza de prueba',
            'sku' => 'CE-PRUEBA',
        ]);
    }

    public function test_inventory_inbound_movement_updates_stock(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $producto = Producto::query()->create([
            'categoria_producto_id' => CategoriaProducto::query()->firstOrFail()->id,
            'unidad_inventario_id' => UnidadInventario::query()->where('codigo', 'ud')->firstOrFail()->id,
            'nombre' => 'Refresco prueba',
            'sku' => 'REF-PRUEBA',
            'precio_venta' => 1.80,
            'precio_coste' => 0.70,
            'cantidad_alerta_stock' => 10,
            'controla_stock' => true,
            'controla_caducidad' => false,
            'activo' => true,
        ]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.inventario.productos.stock.movimientos.store', $producto), [
                'tipo' => 'entrada',
                'ubicacion_inventario_id' => $ubicacion->id,
                'cantidad' => 24,
                'motivo' => 'Entrada inicial',
            ])
            ->assertRedirect(route('admin.inventario.productos.stock', $producto));

        $this->assertDatabaseHas('stock_inventario', [
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => 24,
        ]);
    }
}
