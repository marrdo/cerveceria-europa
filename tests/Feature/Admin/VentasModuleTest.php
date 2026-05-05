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
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
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
