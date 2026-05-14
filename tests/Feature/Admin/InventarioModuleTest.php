<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\LoteInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.productos.index'))
            ->assertOk()
            ->assertSee('Productos');
    }

    public function test_inventory_dashboard_can_be_rendered(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.index'))
            ->assertOk()
            ->assertSee('Acciones rapidas')
            ->assertSee('Productos activos')
            ->assertSee('Entradas vs salidas')
            ->assertSee('Movimientos por tipo')
            ->assertSee('Salidas por categoria')
            ->assertSee('Stock por ubicacion')
            ->assertSee('Reposicion urgente')
            ->assertSee('Stock parado')
            ->assertSee('Ultimos movimientos');
    }

    public function test_product_can_be_created(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
            ->get(route('admin.inventario.productos.stock', $producto->sku))
            ->assertOk()
            ->assertSee('Stock de Tomates')
            ->assertSee('Sin stock');
    }

    public function test_product_quantities_use_readable_unit_names(): void
    {
        $this->seed(InventarioSeeder::class);

        $producto = Producto::query()->create([
            'categoria_producto_id' => CategoriaProducto::query()->firstOrFail()->id,
            'unidad_inventario_id' => UnidadInventario::query()->where('codigo', 'botella')->firstOrFail()->id,
            'nombre' => 'Botella prueba plural',
            'sku' => 'BOT-PLURAL',
            'precio_venta' => 3.50,
            'precio_coste' => 1.20,
            'cantidad_alerta_stock' => 6,
            'controla_stock' => true,
            'controla_caducidad' => false,
            'activo' => true,
        ])->load('unidad');

        $this->assertSame('1 botella', $producto->formatearCantidadConUnidad(1));
        $this->assertSame('2 botellas', $producto->formatearCantidadConUnidad(2));
    }

    public function test_supplier_document_must_be_valid_spanish_dni_nie_or_cif(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
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
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
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

    public function test_stock_alerts_screen_shows_low_stock_and_empty_stock_products(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();

        $productoSinStock = $this->crearProductoPrueba([
            'nombre' => 'Barril sin stock alerta',
            'sku' => 'ALERTA-SIN-STOCK',
            'cantidad_alerta_stock' => 5,
        ]);

        $productoBajoStock = $this->crearProductoPrueba([
            'nombre' => 'Botella bajo stock alerta',
            'sku' => 'ALERTA-BAJO-STOCK',
            'cantidad_alerta_stock' => 10,
        ]);

        StockInventario::query()->create([
            'producto_id' => $productoBajoStock->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => 3,
            'cantidad_minima' => 0,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.alertas.index'))
            ->assertOk()
            ->assertSee($productoSinStock->nombre)
            ->assertSee($productoBajoStock->nombre)
            ->assertSee('Sin stock')
            ->assertSee('Stock bajo');
    }

    public function test_movements_report_can_filter_by_product_and_type(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $productoIncluido = $this->crearProductoPrueba(['nombre' => 'Producto incluido informe']);
        $productoExcluido = $this->crearProductoPrueba(['nombre' => 'Producto excluido informe']);

        MovimientoInventario::query()->create([
            'producto_id' => $productoIncluido->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'tipo' => 'entrada',
            'cantidad' => 5,
            'stock_antes' => 0,
            'stock_despues' => 5,
            'motivo' => 'Movimiento incluido fase 1.2',
        ]);

        MovimientoInventario::query()->create([
            'producto_id' => $productoExcluido->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'tipo' => 'salida',
            'cantidad' => 2,
            'stock_antes' => 5,
            'stock_despues' => 3,
            'motivo' => 'Movimiento excluido fase 1.2',
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.movimientos.index', [
                'producto_id' => $productoIncluido->id,
                'tipo' => 'entrada',
            ]))
            ->assertOk()
            ->assertSee('Movimiento incluido fase 1.2')
            ->assertDontSee('Movimiento excluido fase 1.2');
    }

    public function test_movements_report_shows_the_user_who_registered_the_movement(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create([
            'nombre' => 'Encargado Movimientos',
            'email' => 'encargado.movimientos@cerveceria-europa.local',
            'rol' => RolUsuario::Encargado,
        ]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $producto = $this->crearProductoPrueba(['nombre' => 'Producto con usuario movimiento']);

        $this->actingAs($usuario)
            ->post(route('admin.inventario.productos.stock.movimientos.store', $producto), [
                'tipo' => 'entrada',
                'ubicacion_inventario_id' => $ubicacion->id,
                'cantidad' => 4,
                'motivo' => 'Entrada con usuario registrado',
            ])
            ->assertRedirect(route('admin.inventario.productos.stock', $producto));

        $this->actingAs($usuario)
            ->get(route('admin.inventario.movimientos.index'))
            ->assertOk()
            ->assertSee('Entrada con usuario registrado')
            ->assertSee('Encargado Movimientos')
            ->assertSee('encargado.movimientos@cerveceria-europa.local');
    }

    public function test_products_can_be_exported_as_utf8_csv(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $producto = $this->crearProductoPrueba(['nombre' => 'Producto CSV fase 1.2']);

        $response = $this->actingAs($usuario)
            ->get(route('admin.inventario.productos.exportar'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contenido = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $contenido);
        $this->assertStringContainsString('Nombre;SKU;Categoria;Proveedor;Unidad;Stock;Estado', $contenido);
        $this->assertStringContainsString($producto->nombre, $contenido);
    }

    public function test_movements_can_be_exported_as_utf8_csv_with_filters(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $producto = $this->crearProductoPrueba(['nombre' => 'Producto movimiento CSV']);

        MovimientoInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'tipo' => 'entrada',
            'cantidad' => 7,
            'stock_antes' => 0,
            'stock_despues' => 7,
            'motivo' => 'Movimiento CSV fase 1.2',
        ]);

        $response = $this->actingAs($usuario)
            ->get(route('admin.inventario.movimientos.exportar', [
                'producto_id' => $producto->id,
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contenido = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $contenido);
        $this->assertStringContainsString('Fecha;Usuario;Producto;Tipo;Cantidad;Unidad', $contenido);
        $this->assertStringContainsString('Movimiento CSV fase 1.2', $contenido);
    }

    public function test_stock_alerts_can_be_exported_as_utf8_csv(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $producto = $this->crearProductoPrueba([
            'nombre' => 'Producto alerta CSV',
            'cantidad_alerta_stock' => 8,
        ]);

        $response = $this->actingAs($usuario)
            ->get(route('admin.inventario.alertas.exportar'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $contenido = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $contenido);
        $this->assertStringContainsString('Producto;SKU;Categoria;Proveedor;Stock;Unidad;Alerta;Estado', $contenido);
        $this->assertStringContainsString($producto->nombre, $contenido);
    }

    public function test_expiry_date_is_required_for_inbound_movement_when_product_tracks_expiry(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $producto = $this->crearProductoPrueba([
            'nombre' => 'Producto caducidad obligatoria',
            'controla_caducidad' => true,
        ]);

        $this->actingAs($usuario)
            ->from(route('admin.inventario.productos.stock', $producto))
            ->post(route('admin.inventario.productos.stock.movimientos.store', $producto), [
                'tipo' => 'entrada',
                'ubicacion_inventario_id' => $ubicacion->id,
                'cantidad' => 6,
                'motivo' => 'Entrada sin caducidad',
            ])
            ->assertRedirect(route('admin.inventario.productos.stock', $producto))
            ->assertSessionHasErrors([
                'caduca_el' => 'El campo fecha de caducidad es obligatorio para entradas de productos con caducidad.',
            ]);
    }

    public function test_inbound_movement_creates_inventory_lot(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'CAMARA_FRIA')->firstOrFail();
        $producto = $this->crearProductoPrueba([
            'nombre' => 'Producto lote entrada',
            'controla_caducidad' => true,
        ]);

        $this->actingAs($usuario)
            ->post(route('admin.inventario.productos.stock.movimientos.store', $producto), [
                'tipo' => 'entrada',
                'ubicacion_inventario_id' => $ubicacion->id,
                'cantidad' => 12,
                'motivo' => 'Entrada con lote',
                'codigo_lote' => 'LOTE-IPA-001',
                'caduca_el' => now()->addMonth()->toDateString(),
            ])
            ->assertRedirect(route('admin.inventario.productos.stock', $producto));

        $this->assertDatabaseHas('lotes_inventario', [
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'codigo_lote' => 'LOTE-IPA-001',
            'cantidad_inicial' => 12,
            'cantidad_disponible' => 12,
        ]);
    }

    public function test_outbound_movement_consumes_earliest_expiring_lot_first(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'CAMARA_FRIA')->firstOrFail();
        $producto = $this->crearProductoPrueba([
            'nombre' => 'Producto consumo FEFO',
            'controla_caducidad' => true,
        ]);

        StockInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => 10,
            'cantidad_minima' => 0,
        ]);

        $loteAntiguo = LoteInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'codigo_lote' => 'LOTE-FEFO-1',
            'cantidad_inicial' => 5,
            'cantidad_disponible' => 5,
            'recibido_el' => now()->subDays(10)->toDateString(),
            'caduca_el' => now()->addDays(5)->toDateString(),
            'activo' => true,
        ]);

        $loteNuevo = LoteInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'codigo_lote' => 'LOTE-FEFO-2',
            'cantidad_inicial' => 5,
            'cantidad_disponible' => 5,
            'recibido_el' => now()->subDays(5)->toDateString(),
            'caduca_el' => now()->addDays(20)->toDateString(),
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->post(route('admin.inventario.productos.stock.movimientos.store', $producto), [
                'tipo' => 'salida',
                'ubicacion_inventario_id' => $ubicacion->id,
                'cantidad' => 6,
                'motivo' => 'Salida FEFO',
            ])
            ->assertRedirect(route('admin.inventario.productos.stock', $producto));

        $this->assertSame('0.000', $loteAntiguo->refresh()->cantidad_disponible);
        $this->assertSame('4.000', $loteNuevo->refresh()->cantidad_disponible);
    }

    public function test_stock_alerts_screen_shows_expired_and_near_expiry_lots(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $ubicacion = UbicacionInventario::query()->where('codigo', 'COCINA')->firstOrFail();
        $producto = $this->crearProductoPrueba([
            'nombre' => 'Producto alerta caducidad',
            'controla_caducidad' => true,
        ]);

        LoteInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'codigo_lote' => 'LOTE-CADUCADO',
            'cantidad_inicial' => 2,
            'cantidad_disponible' => 2,
            'recibido_el' => now()->subMonth()->toDateString(),
            'caduca_el' => now()->subDay()->toDateString(),
            'activo' => true,
        ]);

        LoteInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'codigo_lote' => 'LOTE-PROXIMO',
            'cantidad_inicial' => 3,
            'cantidad_disponible' => 3,
            'recibido_el' => now()->subWeek()->toDateString(),
            'caduca_el' => now()->addDays(7)->toDateString(),
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.alertas.index'))
            ->assertOk()
            ->assertSee('Lotes caducados')
            ->assertSee('Lotes proximos a caducar')
            ->assertSee('LOTE-CADUCADO')
            ->assertSee('LOTE-PROXIMO');
    }

    /**
     * Crea un producto de prueba con valores por defecto solidos para inventario.
     *
     * @param array<string, mixed> $sobrescribir
     */
    private function crearProductoPrueba(array $sobrescribir = []): Producto
    {
        return Producto::query()->create(array_merge([
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
        ], $sobrescribir));
    }

}
