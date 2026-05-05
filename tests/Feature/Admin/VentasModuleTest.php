<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\StockInventario;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Modulos\WebPublica\Models\TarifaContenidoWeb;
use Database\Seeders\InventarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentasModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiter_can_create_order_from_public_menu_content(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);
        [$contenido, $tarifa, $ubicacion] = $this->crearContenidoVendibleConStock(8);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.store'), [
                'mesa' => '4',
                'ubicacion_inventario_id' => $ubicacion->id,
                'lineas' => [
                    [
                        'contenido_web_id' => $contenido->id,
                        'tarifa_contenido_web_id' => $tarifa->id,
                        'cantidad' => 2,
                    ],
                ],
            ])
            ->assertRedirect();

        $comanda = Comanda::query()->with('lineas')->firstOrFail();

        $this->assertSame('4', $comanda->mesa);
        $this->assertSame(EstadoComanda::Abierta, $comanda->estado);
        $this->assertSame(1, $comanda->lineas->count());
        $this->assertSame('Cerveza Leffe (Botella)', $comanda->lineas->first()->nombre);
        $this->assertSame('7.00', (string) $comanda->total);
    }

    public function test_create_order_screen_groups_menu_by_parent_and_child_categories(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);
        [$contenido] = $this->crearContenidoVendibleConStock(8);

        $padre = CategoriaCarta::query()->create([
            'nombre' => 'Cervezas',
            'slug' => 'cervezas',
            'activo' => true,
            'orden' => 1,
        ]);
        $hija = CategoriaCarta::query()->create([
            'categoria_padre_id' => $padre->id,
            'nombre' => 'Gourmet',
            'slug' => 'gourmet',
            'activo' => true,
            'orden' => 2,
        ]);

        $contenido->update(['categoria_carta_id' => $hija->id]);

        $this->actingAs($usuario)
            ->get(route('admin.ventas.comandas.create'))
            ->assertOk()
            ->assertSee('Cervezas')
            ->assertSee('Gourmet')
            ->assertSee('Sumar unidad')
            ->assertSee('Restar unidad');
    }

    public function test_order_index_can_filter_by_multiple_states(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        Comanda::query()->create([
            'numero' => 'COM-TEST-ABIERTA',
            'estado' => EstadoComanda::Abierta,
            'total' => 1,
        ]);
        Comanda::query()->create([
            'numero' => 'COM-TEST-PREPARACION',
            'estado' => EstadoComanda::EnPreparacion,
            'total' => 2,
        ]);
        Comanda::query()->create([
            'numero' => 'COM-TEST-PAGADA',
            'estado' => EstadoComanda::Pagada,
            'total' => 3,
        ]);

        $this->actingAs($usuario)
            ->get(route('admin.ventas.comandas.index', [
                'estado' => [
                    EstadoComanda::Abierta->value,
                    EstadoComanda::EnPreparacion->value,
                ],
            ]))
            ->assertOk()
            ->assertSee('name="estado[]"', false)
            ->assertSee('COM-TEST-ABIERTA')
            ->assertSee('COM-TEST-PREPARACION')
            ->assertDontSee('COM-TEST-PAGADA');
    }

    public function test_serving_order_line_deducts_inventory_stock_once(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);
        [$contenido, $tarifa, $ubicacion, $producto] = $this->crearContenidoVendibleConStock(8);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.store'), [
                'mesa' => 'Barra',
                'ubicacion_inventario_id' => $ubicacion->id,
                'lineas' => [
                    [
                        'contenido_web_id' => $contenido->id,
                        'tarifa_contenido_web_id' => $tarifa->id,
                        'cantidad' => 3,
                    ],
                ],
            ]);

        $comanda = Comanda::query()->with('lineas')->firstOrFail();
        $linea = $comanda->lineas->first();

        $this->actingAs($usuario)
            ->patch(route('admin.ventas.comandas.lineas.servir', [$comanda, $linea]))
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda));

        $this->assertSame(5.0, (float) StockInventario::query()
            ->where('producto_id', $producto->id)
            ->where('ubicacion_inventario_id', $ubicacion->id)
            ->value('cantidad'));

        $this->assertDatabaseHas('lineas_comanda', [
            'id' => $linea->id,
            'estado' => EstadoLineaComanda::Servida->value,
        ]);

        $this->assertDatabaseHas('comandas', [
            'id' => $comanda->id,
            'estado' => EstadoComanda::Servida->value,
        ]);

        $this->assertSame(1, MovimientoInventario::query()->where('producto_id', $producto->id)->count());

        $this->actingAs($usuario)
            ->patch(route('admin.ventas.comandas.lineas.servir', [$comanda, $linea]))
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda));

        $this->assertSame(1, MovimientoInventario::query()->where('producto_id', $producto->id)->count());
        $this->assertSame(5.0, (float) StockInventario::query()
            ->where('producto_id', $producto->id)
            ->where('ubicacion_inventario_id', $ubicacion->id)
            ->value('cantidad'));
    }

    public function test_open_order_cannot_be_paid_before_being_served(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);
        [$contenido, $tarifa, $ubicacion] = $this->crearContenidoVendibleConStock(8);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.store'), [
                'mesa' => '2',
                'ubicacion_inventario_id' => $ubicacion->id,
                'lineas' => [
                    [
                        'contenido_web_id' => $contenido->id,
                        'tarifa_contenido_web_id' => $tarifa->id,
                        'cantidad' => 1,
                    ],
                ],
            ]);

        $comanda = Comanda::query()->firstOrFail();

        $this->actingAs($usuario)
            ->from(route('admin.ventas.comandas.show', $comanda))
            ->post(route('admin.ventas.comandas.pagos.store', $comanda), [
                'metodo' => MetodoPagoComanda::Tarjeta->value,
                'importe' => 3.50,
            ])
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda))
            ->assertSessionHasErrors('estado');
    }

    public function test_partial_payment_keeps_order_served_and_final_cash_payment_marks_it_paid(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);
        [$contenido, $tarifa, $ubicacion] = $this->crearContenidoVendibleConStock(8);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.store'), [
                'mesa' => 'Caja',
                'ubicacion_inventario_id' => $ubicacion->id,
                'lineas' => [
                    [
                        'contenido_web_id' => $contenido->id,
                        'tarifa_contenido_web_id' => $tarifa->id,
                        'cantidad' => 2,
                    ],
                ],
            ]);

        $comanda = Comanda::query()->with('lineas')->firstOrFail();
        $linea = $comanda->lineas->first();

        $this->actingAs($usuario)
            ->patch(route('admin.ventas.comandas.lineas.servir', [$comanda, $linea]));

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.pagos.store', $comanda), [
                'metodo' => MetodoPagoComanda::Tarjeta->value,
                'importe' => 3,
                'referencia' => 'TPV-123',
            ])
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda));

        $this->assertDatabaseHas('comandas', [
            'id' => $comanda->id,
            'estado' => EstadoComanda::Servida->value,
        ]);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.pagos.store', $comanda), [
                'metodo' => MetodoPagoComanda::Efectivo->value,
                'importe' => 4,
                'recibido' => 5,
            ])
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda));

        $this->assertDatabaseHas('comandas', [
            'id' => $comanda->id,
            'estado' => EstadoComanda::Pagada->value,
        ]);

        $this->assertDatabaseHas('pagos_comanda', [
            'comanda_id' => $comanda->id,
            'metodo' => MetodoPagoComanda::Efectivo->value,
            'importe' => 4,
            'recibido' => 5,
            'cambio' => 1,
        ]);

        $this->assertSame(7.0, Comanda::query()->with('pagos')->findOrFail($comanda->id)->totalPagado());
    }

    public function test_pending_order_lines_can_be_updated_and_cancelled_operatively(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);
        [$contenido, $tarifa, $ubicacion] = $this->crearContenidoVendibleConStock(8);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.store'), [
                'mesa' => '3',
                'ubicacion_inventario_id' => $ubicacion->id,
                'lineas' => [
                    [
                        'contenido_web_id' => $contenido->id,
                        'tarifa_contenido_web_id' => $tarifa->id,
                        'cantidad' => 2,
                    ],
                ],
            ]);

        $comanda = Comanda::query()->with('lineas')->firstOrFail();
        $linea = $comanda->lineas->first();

        $this->actingAs($usuario)
            ->patch(route('admin.ventas.comandas.operativa.update', $comanda), [
                'mesa' => 'Barra',
                'ubicacion_inventario_id' => '',
                'lineas' => [
                    $linea->id => [
                        'cantidad' => 3,
                        'notas' => 'Sin vaso frio',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda));

        $linea->refresh();
        $comanda->refresh();

        $this->assertSame('Barra', $comanda->mesa);
        $this->assertNull($comanda->ubicacion_inventario_id);
        $this->assertSame('3.000', (string) $linea->cantidad);
        $this->assertSame('Sin vaso frio', $linea->notas);
        $this->assertSame('10.50', (string) $comanda->total);

        $this->actingAs($usuario)
            ->patch(route('admin.ventas.comandas.operativa.update', $comanda), [
                'mesa' => 'Barra',
                'lineas' => [
                    $linea->id => [
                        'cantidad' => 3,
                        'cancelar' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda));

        $this->assertDatabaseHas('lineas_comanda', [
            'id' => $linea->id,
            'estado' => EstadoLineaComanda::Cancelada->value,
        ]);
        $this->assertDatabaseHas('comandas', [
            'id' => $comanda->id,
            'estado' => EstadoComanda::Cancelada->value,
            'total' => 0,
        ]);
    }

    public function test_served_order_can_receive_more_lines_before_payment(): void
    {
        $this->prepararModuloVentas();
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);
        [$contenido, $tarifa, $ubicacion] = $this->crearContenidoVendibleConStock(8);

        $extra = ContenidoWeb::query()->create([
            'tipo' => TipoContenidoWeb::Cerveza,
            'titulo' => 'Cerveza Extra',
            'slug' => 'cerveza-extra',
            'precio' => 2.50,
            'publicado' => true,
            'orden' => 2,
        ]);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.store'), [
                'mesa' => '5',
                'ubicacion_inventario_id' => $ubicacion->id,
                'lineas' => [
                    [
                        'contenido_web_id' => $contenido->id,
                        'tarifa_contenido_web_id' => $tarifa->id,
                        'cantidad' => 1,
                    ],
                ],
            ]);

        $comanda = Comanda::query()->with('lineas')->firstOrFail();
        $linea = $comanda->lineas->first();

        $this->actingAs($usuario)
            ->patch(route('admin.ventas.comandas.lineas.servir', [$comanda, $linea]));

        $this->assertDatabaseHas('comandas', [
            'id' => $comanda->id,
            'estado' => EstadoComanda::Servida->value,
        ]);

        $this->actingAs($usuario)
            ->post(route('admin.ventas.comandas.lineas.store', $comanda), [
                'lineas' => [
                    [
                        'contenido_web_id' => $extra->id,
                        'cantidad' => 2,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.ventas.comandas.show', $comanda));

        $comanda->refresh();

        $this->assertSame(2, $comanda->lineas()->count());
        $this->assertSame(EstadoComanda::EnPreparacion, $comanda->estado);
        $this->assertSame('8.50', (string) $comanda->total);
    }

    private function prepararModuloVentas(): void
    {
        $this->seed(InventarioSeeder::class);

        Modulo::query()->create([
            'clave' => 'ventas',
            'nombre' => 'Ventas',
            'activo' => true,
        ]);
    }

    /**
     * @return array{0: ContenidoWeb, 1: TarifaContenidoWeb, 2: UbicacionInventario, 3: Producto}
     */
    private function crearContenidoVendibleConStock(float $stockInicial): array
    {
        $producto = Producto::query()->create([
            'categoria_producto_id' => CategoriaProducto::query()->firstOrFail()->id,
            'unidad_inventario_id' => UnidadInventario::query()->where('codigo', 'ud')->firstOrFail()->id,
            'nombre' => 'Botella Leffe',
            'sku' => 'LEFFE-BOTELLA',
            'precio_venta' => 3.50,
            'precio_coste' => 1.40,
            'cantidad_alerta_stock' => 2,
            'controla_stock' => true,
            'controla_caducidad' => false,
            'activo' => true,
        ]);

        $ubicacion = UbicacionInventario::query()->where('codigo', 'BARRA')->firstOrFail();

        StockInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => $stockInicial,
            'cantidad_minima' => 0,
        ]);

        $contenido = ContenidoWeb::query()->create([
            'tipo' => TipoContenidoWeb::Cerveza,
            'producto_id' => $producto->id,
            'titulo' => 'Cerveza Leffe',
            'slug' => 'cerveza-leffe',
            'precio' => 3.50,
            'publicado' => true,
            'orden' => 1,
        ]);

        $tarifa = $contenido->tarifas()->create([
            'nombre' => 'Botella',
            'precio' => 3.50,
            'orden' => 1,
        ]);

        return [$contenido, $tarifa, $ubicacion, $producto];
    }
}
