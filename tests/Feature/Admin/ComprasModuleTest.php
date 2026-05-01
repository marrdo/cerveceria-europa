<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Models\EventoPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\UnidadInventario;
use App\Models\Usuario;
use Database\Seeders\InventarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprasModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_list_can_be_rendered(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->get(route('admin.compras.pedidos.index'))
            ->assertOk()
            ->assertSee('Pedidos');
    }

    public function test_purchase_order_can_be_created_with_lines_and_totals(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.store'), [
                'proveedor_id' => $proveedor->id,
                'fecha_pedido' => '2026-05-01',
                'fecha_prevista' => '2026-05-03',
                'notas' => 'Pedido de prueba fase 2.0',
                'lineas' => [
                    [
                        'producto_id' => $producto->id,
                        'descripcion' => 'Linea cerveza artesana',
                        'cantidad' => 10,
                        'coste_unitario' => 2.50,
                        'iva_porcentaje' => 21,
                    ],
                ],
            ])
            ->assertRedirect();

        $pedido = PedidoCompra::query()->with('lineas')->firstOrFail();

        $this->assertSame(EstadoPedidoCompra::Borrador, $pedido->estado);
        $this->assertSame('25.00', $pedido->subtotal);
        $this->assertSame('5.25', $pedido->impuestos);
        $this->assertSame('30.25', $pedido->total);
        $this->assertSame($usuario->id, $pedido->creado_por);
        $this->assertCount(1, $pedido->lineas);

        $this->assertDatabaseHas('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'creado',
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_purchase_order_can_be_created_with_more_than_five_lines(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $lineas = [];

        for ($indice = 0; $indice < 8; $indice++) {
            $producto = $this->crearProductoPrueba();
            $lineas[] = [
                'producto_id' => $producto->id,
                'descripcion' => 'Linea multiple '.$indice,
                'cantidad' => 1,
                'coste_unitario' => 2,
                'iva_porcentaje' => 21,
            ];
        }

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.store'), [
                'proveedor_id' => $proveedor->id,
                'fecha_pedido' => '2026-05-01',
                'lineas' => $lineas,
            ])
            ->assertRedirect();

        $pedido = PedidoCompra::query()->with('lineas')->firstOrFail();

        $this->assertCount(8, $pedido->lineas);
        $this->assertSame('16.00', $pedido->subtotal);
        $this->assertSame('3.36', $pedido->impuestos);
        $this->assertSame('19.36', $pedido->total);
    }

    public function test_purchase_order_can_be_updated_while_draft(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);

        $this->actingAs($usuario)
            ->put(route('admin.compras.pedidos.update', $pedido), [
                'proveedor_id' => $proveedor->id,
                'fecha_pedido' => '2026-05-01',
                'fecha_prevista' => '2026-05-04',
                'lineas' => [
                    [
                        'producto_id' => $producto->id,
                        'descripcion' => 'Linea actualizada',
                        'cantidad' => 4,
                        'coste_unitario' => 3,
                        'iva_porcentaje' => 10,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $pedido->refresh();

        $this->assertSame('12.00', $pedido->subtotal);
        $this->assertSame('1.20', $pedido->impuestos);
        $this->assertSame('13.20', $pedido->total);
        $this->assertDatabaseHas('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'actualizado',
        ]);
    }

    public function test_purchase_order_cannot_be_edited_when_not_draft(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);

        $this->actingAs($usuario)
            ->put(route('admin.compras.pedidos.update', $pedido), [
                'proveedor_id' => $proveedor->id,
                'lineas' => [
                    [
                        'producto_id' => $producto->id,
                        'cantidad' => 9,
                        'coste_unitario' => 9,
                        'iva_porcentaje' => 21,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertSame('2.00', $pedido->refresh()->subtotal);
    }

    public function test_purchase_order_state_change_registers_event(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);

        $this->actingAs($usuario)
            ->patch(route('admin.compras.pedidos.estado', $pedido), [
                'estado' => EstadoPedidoCompra::Pedido->value,
                'descripcion' => 'Pedido enviado al proveedor',
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertSame(EstadoPedidoCompra::Pedido, $pedido->refresh()->estado);
        $this->assertDatabaseHas('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'cambio_estado',
            'estado_anterior' => EstadoPedidoCompra::Borrador->value,
            'estado_nuevo' => EstadoPedidoCompra::Pedido->value,
            'descripcion' => 'Pedido enviado al proveedor',
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_purchase_order_requires_at_least_one_complete_line(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();

        $this->actingAs($usuario)
            ->from(route('admin.compras.pedidos.create'))
            ->post(route('admin.compras.pedidos.store'), [
                'proveedor_id' => $proveedor->id,
                'lineas' => [
                    [
                        'producto_id' => '',
                        'descripcion' => 'Linea incompleta',
                        'cantidad' => '',
                        'coste_unitario' => 2,
                        'iva_porcentaje' => 21,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.compras.pedidos.create'))
            ->assertSessionHasErrors([
                'lineas.0.producto_id' => 'Cada linea usada debe tener producto y cantidad mayor que cero.',
            ]);
    }

    private function crearProductoPrueba(): Producto
    {
        return Producto::query()->create([
            'categoria_producto_id' => CategoriaProducto::query()->firstOrFail()->id,
            'unidad_inventario_id' => UnidadInventario::query()->where('codigo', 'ud')->firstOrFail()->id,
            'nombre' => 'Cerveza compra prueba',
            'sku' => 'COMPRA-'.str()->random(6),
            'precio_venta' => 4.50,
            'precio_coste' => 2.00,
            'cantidad_alerta_stock' => 10,
            'controla_stock' => true,
            'controla_caducidad' => false,
            'activo' => true,
        ]);
    }

    private function crearPedidoBorrador(Usuario $usuario, Proveedor $proveedor, Producto $producto): PedidoCompra
    {
        $pedido = PedidoCompra::query()->create([
            'proveedor_id' => $proveedor->id,
            'numero' => 'PC-TEST-'.str()->random(6),
            'estado' => EstadoPedidoCompra::Borrador,
            'subtotal' => 2,
            'impuestos' => 0.42,
            'total' => 2.42,
            'creado_por' => $usuario->id,
            'actualizado_por' => $usuario->id,
        ]);

        $pedido->lineas()->create([
            'producto_id' => $producto->id,
            'descripcion' => $producto->nombre,
            'cantidad' => 1,
            'coste_unitario' => 2,
            'iva_porcentaje' => 21,
            'subtotal' => 2,
            'impuestos' => 0.42,
            'total' => 2.42,
            'orden' => 0,
        ]);

        EventoPedidoCompra::query()->create([
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'creado',
            'descripcion' => 'Pedido creado en test.',
            'usuario_id' => $usuario->id,
        ]);

        return $pedido;
    }
}
