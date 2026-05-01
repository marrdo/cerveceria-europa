<?php

namespace Tests\Feature\Admin;

use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\StockInventario;
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

    public function test_product_stock_screen_can_be_rendered(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $producto = Producto::query()->create([
            'categoria_producto_id' => CategoriaProducto::query()->firstOrFail()->id,
            'unidad_inventario_id' => UnidadInventario::query()->where('codigo', 'ud')->firstOrFail()->id,
            'nombre' => 'Tomates',
            'sku' => 'TMT',
            'precio_venta' => 1.50,
            'precio_coste' => 1.00,
            'cantidad_alerta_stock' => 5,
            'controla_stock' => true,
            'controla_caducidad' => true,
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.productos.stock', $producto))
            ->assertOk()
            ->assertSee('Stock de Tomates')
            ->assertSee('Sin stock');
    }

    public function test_supplier_document_must_be_valid_spanish_dni_nie_or_cif(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)
            ->from(route('admin.inventario.proveedores.create'))
            ->post(route('admin.inventario.proveedores.store'), [
                'nombre' => 'Proveedor documento invalido',
                'cif_nif' => '12345678A',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.inventario.proveedores.create'))
            ->assertSessionHasErrors([
                'cif_nif' => 'El campo CIF/NIF debe ser un DNI, NIE o CIF espanol valido.',
            ]);
    }

    public function test_supplier_document_is_normalized_when_valid(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)
            ->post(route('admin.inventario.proveedores.store'), [
                'nombre' => 'Proveedor con CIF',
                'cif_nif' => ' b-99286320 ',
                'email' => 'CONTACTO@PROVEEDOR.ES',
                'telefono' => '+34 600 123 456',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.inventario.proveedores.index'));

        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Proveedor con CIF',
            'cif_nif' => 'B99286320',
            'email' => 'contacto@proveedor.es',
            'telefono' => '+34600123456',
        ]);

        $this->assertTrue(Proveedor::query()->where('cif_nif', 'B99286320')->exists());
    }

    public function test_supplier_email_must_have_valid_format(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)
            ->from(route('admin.inventario.proveedores.create'))
            ->post(route('admin.inventario.proveedores.store'), [
                'nombre' => 'Proveedor email invalido',
                'email' => 'correo-no-valido',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.inventario.proveedores.create'))
            ->assertSessionHasErrors([
                'email' => 'El campo correo electronico debe ser un correo electronico valido.',
            ]);
    }

    public function test_supplier_phone_must_be_valid_spanish_phone(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)
            ->from(route('admin.inventario.proveedores.create'))
            ->post(route('admin.inventario.proveedores.store'), [
                'nombre' => 'Proveedor telefono invalido',
                'telefono' => '12345',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.inventario.proveedores.create'))
            ->assertSessionHasErrors([
                'telefono' => 'El campo telefono debe ser un numero espanol valido.',
            ]);
    }

    public function test_product_filters_can_filter_by_search_provider_category_stock_status_and_active_state(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        $categoriaCervezas = CategoriaProducto::query()->where('nombre', 'Cervezas')->firstOrFail();
        $categoriaComida = CategoriaProducto::query()->where('nombre', 'Alimentacion')->firstOrFail();
        $unidad = UnidadInventario::query()->where('codigo', 'ud')->firstOrFail();
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $proveedorNorte = Proveedor::query()->create(['nombre' => 'Distribuidor Norte', 'slug' => 'distribuidor-norte', 'activo' => true]);
        $proveedorSur = Proveedor::query()->create(['nombre' => 'Distribuidor Sur', 'slug' => 'distribuidor-sur', 'activo' => true]);

        $productoBajo = Producto::query()->create([
            'categoria_producto_id' => $categoriaCervezas->id,
            'proveedor_id' => $proveedorNorte->id,
            'unidad_inventario_id' => $unidad->id,
            'nombre' => 'Lupulo Amarillo Especial',
            'sku' => 'LUP-AMARILLO',
            'precio_venta' => 4.20,
            'cantidad_alerta_stock' => 10,
            'controla_stock' => true,
            'activo' => true,
        ]);

        $productoCorrecto = Producto::query()->create([
            'categoria_producto_id' => $categoriaComida->id,
            'proveedor_id' => $proveedorSur->id,
            'unidad_inventario_id' => $unidad->id,
            'nombre' => 'Malta Negra Reserva',
            'sku' => 'MALTA-NEGRA',
            'precio_venta' => 3.10,
            'cantidad_alerta_stock' => 5,
            'controla_stock' => true,
            'activo' => false,
        ]);

        StockInventario::query()->create([
            'producto_id' => $productoBajo->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => 4,
            'cantidad_minima' => 0,
        ]);

        StockInventario::query()->create([
            'producto_id' => $productoCorrecto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => 20,
            'cantidad_minima' => 0,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.productos.index', [
                'busqueda' => 'Lupulo',
                'categoria_producto_id' => $categoriaCervezas->id,
                'proveedor_id' => $proveedorNorte->id,
                'estado_stock' => 'bajo',
                'activo' => '1',
            ]))
            ->assertOk()
            ->assertSee('Lupulo Amarillo Especial')
            ->assertDontSee('Malta Negra Reserva');
    }

    public function test_catalog_filters_can_filter_by_name_contact_and_active_state(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        Proveedor::query()->create([
            'nombre' => 'Mayorista Norte Filtro',
            'slug' => 'mayorista-norte-filtro',
            'email' => 'norte@proveedor.es',
            'telefono' => '+34600111222',
            'activo' => true,
        ]);

        Proveedor::query()->create([
            'nombre' => 'Mayorista Sur Filtro',
            'slug' => 'mayorista-sur-filtro',
            'email' => 'sur@proveedor.es',
            'telefono' => '+34600333444',
            'activo' => false,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.proveedores.index', [
                'busqueda' => 'Mayorista',
                'contacto' => 'norte@proveedor.es',
                'activo' => '1',
            ]))
            ->assertOk()
            ->assertSee('Mayorista Norte Filtro')
            ->assertDontSee('Mayorista Sur Filtro');
    }

    public function test_catalog_filters_work_for_codes_and_inactive_records(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();

        UnidadInventario::query()->create([
            'nombre' => 'Caja filtrable',
            'codigo' => 'CJ-FILTRO',
            'permite_decimal' => false,
            'activo' => false,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.unidades.index', [
                'contacto' => 'CJ-FILTRO',
                'activo' => '0',
            ]))
            ->assertOk()
            ->assertSee('Caja filtrable');
    }

    public function test_transfer_movement_requires_different_origin_and_destination(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $producto = $this->crearProductoPrueba();

        $this->actingAs($usuario)
            ->from(route('admin.inventario.productos.stock', $producto))
            ->post(route('admin.inventario.productos.stock.movimientos.store', $producto), [
                'tipo' => 'transferencia',
                'ubicacion_origen_id' => $ubicacion->id,
                'ubicacion_destino_id' => $ubicacion->id,
                'cantidad' => 1,
                'motivo' => 'Movimiento de prueba',
            ])
            ->assertRedirect(route('admin.inventario.productos.stock', $producto))
            ->assertSessionHasErrors([
                'ubicacion_destino_id' => 'El campo ubicacion de destino debe ser distinto de la ubicacion de origen.',
            ]);
    }

    public function test_non_transfer_movement_requires_main_location(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create();
        $producto = $this->crearProductoPrueba();

        $this->actingAs($usuario)
            ->from(route('admin.inventario.productos.stock', $producto))
            ->post(route('admin.inventario.productos.stock.movimientos.store', $producto), [
                'tipo' => 'salida',
                'cantidad' => 1,
                'motivo' => 'Salida sin ubicacion',
            ])
            ->assertRedirect(route('admin.inventario.productos.stock', $producto))
            ->assertSessionHasErrors([
                'ubicacion_inventario_id' => 'El campo ubicacion es obligatorio para entradas, salidas y ajustes.',
            ]);
    }

    private function crearProductoPrueba(): Producto
    {
        return Producto::query()->create([
            'categoria_producto_id' => CategoriaProducto::query()->firstOrFail()->id,
            'unidad_inventario_id' => UnidadInventario::query()->where('codigo', 'ud')->firstOrFail()->id,
            'nombre' => 'Producto movimiento prueba',
            'sku' => 'MOV-PRUEBA-'.str()->random(6),
            'precio_venta' => 1.80,
            'precio_coste' => 0.70,
            'cantidad_alerta_stock' => 10,
            'controla_stock' => true,
            'controla_caducidad' => false,
            'activo' => true,
        ]);
    }
}
