<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Enums\TipoIncidenciaRecepcionCompra;
use App\Modulos\Compras\Models\EventoPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use App\Models\Usuario;
use Database\Seeders\InventarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
        $this->travelTo(Carbon::parse('2026-05-01 10:00:00'));
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

        $this->assertSame('PC-00001-2026', $pedido->numero);
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

    public function test_purchase_order_number_continues_last_sequence_for_current_year(): void
    {
        $this->travelTo(Carbon::parse('2026-05-01 10:00:00'));
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $this->crearPedidoBorrador($usuario, $proveedor, $producto, 1, 'PC-00007-2026');
        $this->crearPedidoBorrador($usuario, $proveedor, $producto, 1, 'PC-00099-2025');

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.store'), [
                'proveedor_id' => $proveedor->id,
                'fecha_pedido' => '2026-05-01',
                'lineas' => [
                    [
                        'producto_id' => $producto->id,
                        'cantidad' => 1,
                        'coste_unitario' => 2,
                        'iva_porcentaje' => 21,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pedidos_compra', [
            'numero' => 'PC-00008-2026',
        ]);
    }

    public function test_purchase_order_cannot_be_manually_marked_as_received(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);

        $this->actingAs($usuario)
            ->from(route('admin.compras.pedidos.show', $pedido))
            ->patch(route('admin.compras.pedidos.estado', $pedido), [
                'estado' => EstadoPedidoCompra::Recibido->value,
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido))
            ->assertSessionHasErrors(['estado']);

        $this->assertSame(EstadoPedidoCompra::Pedido, $pedido->refresh()->estado);
        $this->assertDatabaseMissing('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'estado_nuevo' => EstadoPedidoCompra::Recibido->value,
        ]);
    }

    public function test_purchase_order_detail_shows_reception_button_when_pending_quantity_exists(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);
        $pedido->update(['estado' => EstadoPedidoCompra::Recibido]);

        $this->actingAs($usuario)
            ->get(route('admin.compras.pedidos.show', $pedido))
            ->assertOk()
            ->assertSee('Registrar recepcion')
            ->assertDontSee('<option value="recibido"', false)
            ->assertDontSee('<option value="recibido_parcial"', false);
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

    public function test_purchase_order_reception_form_can_be_rendered_when_ordered(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);

        $this->actingAs($usuario)
            ->get(route('admin.compras.pedidos.recepciones.create', $pedido))
            ->assertOk()
            ->assertSee('Lineas recibidas')
            ->assertSee($producto->nombre);
    }

    public function test_purchase_reception_creates_inventory_entry_and_marks_order_as_received(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);
        $linea = $pedido->lineas()->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.recepciones.store', $pedido), [
                'fecha_recepcion' => '2026-05-01',
                'lineas' => [
                    [
                        'linea_pedido_compra_id' => $linea->id,
                        'ubicacion_inventario_id' => $ubicacion->id,
                        'cantidad' => 1,
                        'codigo_lote' => 'RC-TEST-001',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertSame(EstadoPedidoCompra::Recibido, $pedido->refresh()->estado);
        $this->assertDatabaseHas('recepciones_compra', [
            'pedido_compra_id' => $pedido->id,
            'recibido_por' => $usuario->id,
        ]);
        $this->assertDatabaseHas('stock_inventario', [
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => 1,
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'proveedor_id' => $proveedor->id,
            'tipo' => 'entrada',
            'cantidad' => 1,
            'creado_por' => $usuario->id,
        ]);
        $this->assertDatabaseHas('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'recepcion',
            'estado_nuevo' => EstadoPedidoCompra::Recibido->value,
        ]);
    }

    public function test_purchase_reception_can_split_same_order_line_between_locations(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $almacen = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $camara = UbicacionInventario::query()->where('codigo', 'CAMARA_FRIA')->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto, 4);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);
        $linea = $pedido->lineas()->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.recepciones.store', $pedido), [
                'fecha_recepcion' => '2026-05-01',
                'lineas' => [
                    [
                        'linea_pedido_compra_id' => $linea->id,
                        'ubicacion_inventario_id' => $almacen->id,
                        'cantidad' => 2,
                    ],
                    [
                        'linea_pedido_compra_id' => $linea->id,
                        'ubicacion_inventario_id' => $camara->id,
                        'cantidad' => 2,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertDatabaseHas('stock_inventario', [
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $almacen->id,
            'cantidad' => 2,
        ]);
        $this->assertDatabaseHas('stock_inventario', [
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $camara->id,
            'cantidad' => 2,
        ]);
        $this->assertSame(2, MovimientoInventario::query()->where('producto_id', $producto->id)->count());
        $this->assertSame(EstadoPedidoCompra::Recibido, $pedido->refresh()->estado);
    }

    public function test_partial_purchase_reception_marks_order_as_partially_received(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto, 5);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);
        $linea = $pedido->lineas()->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.recepciones.store', $pedido), [
                'fecha_recepcion' => '2026-05-01',
                'lineas' => [
                    [
                        'linea_pedido_compra_id' => $linea->id,
                        'ubicacion_inventario_id' => $ubicacion->id,
                        'cantidad' => 2,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertSame(EstadoPedidoCompra::RecibidoParcial, $pedido->refresh()->estado);
        $this->assertSame(3.0, $linea->refresh()->cantidadPendiente());
    }

    public function test_purchase_reception_requires_expiry_for_products_that_track_expiry(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $ubicacion = UbicacionInventario::query()->where('codigo', 'CAMARA_FRIA')->firstOrFail();
        $producto = $this->crearProductoPrueba(['controla_caducidad' => true]);
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);
        $linea = $pedido->lineas()->firstOrFail();

        $this->actingAs($usuario)
            ->from(route('admin.compras.pedidos.recepciones.create', $pedido))
            ->post(route('admin.compras.pedidos.recepciones.store', $pedido), [
                'fecha_recepcion' => '2026-05-01',
                'lineas' => [
                    [
                        'linea_pedido_compra_id' => $linea->id,
                        'ubicacion_inventario_id' => $ubicacion->id,
                        'cantidad' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.compras.pedidos.recepciones.create', $pedido))
            ->assertSessionHasErrors([
                'lineas.0.caduca_el' => 'La fecha de caducidad es obligatoria para productos con caducidad.',
            ]);
    }

    public function test_purchase_reception_issue_can_be_registered(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto, 5);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);
        $linea = $pedido->lineas()->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.incidencias.store', $pedido), [
                'tipo' => TipoIncidenciaRecepcionCompra::MenosCantidad->value,
                'linea_pedido_compra_id' => $linea->id,
                'cantidad_afectada' => 2,
                'descripcion' => 'El proveedor no entrega dos botellas.',
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertDatabaseHas('incidencias_recepcion_compra', [
            'pedido_compra_id' => $pedido->id,
            'linea_pedido_compra_id' => $linea->id,
            'tipo' => TipoIncidenciaRecepcionCompra::MenosCantidad->value,
            'cantidad_afectada' => 2,
            'descripcion' => 'El proveedor no entrega dos botellas.',
            'registrada_por' => $usuario->id,
        ]);
        $this->assertDatabaseHas('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'incidencia_recepcion',
            'descripcion' => 'Incidencia registrada: Llega menos cantidad.',
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_purchase_reception_issue_must_belong_to_order(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto, 5);
        $otroPedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto, 5);
        $lineaOtroPedido = $otroPedido->lineas()->firstOrFail();

        $this->actingAs($usuario)
            ->from(route('admin.compras.pedidos.show', $pedido))
            ->post(route('admin.compras.pedidos.incidencias.store', $pedido), [
                'tipo' => TipoIncidenciaRecepcionCompra::ProductoEquivocado->value,
                'linea_pedido_compra_id' => $lineaOtroPedido->id,
                'descripcion' => 'Linea de otro pedido.',
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido))
            ->assertSessionHasErrors([
                'linea_pedido_compra_id' => 'La linea seleccionada no pertenece a este pedido.',
            ]);
    }

    public function test_partially_received_order_can_be_closed_with_reason(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $ubicacion = UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto, 5);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);
        $linea = $pedido->lineas()->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('admin.compras.pedidos.recepciones.store', $pedido), [
                'fecha_recepcion' => '2026-05-01',
                'lineas' => [
                    [
                        'linea_pedido_compra_id' => $linea->id,
                        'ubicacion_inventario_id' => $ubicacion->id,
                        'cantidad' => 2,
                    ],
                ],
            ]);

        $this->actingAs($usuario)
            ->patch(route('admin.compras.pedidos.cerrar-pendiente', $pedido), [
                'motivo_cierre' => 'El proveedor no va a servir la cantidad pendiente.',
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertSame(EstadoPedidoCompra::Cerrado, $pedido->refresh()->estado);
        $this->assertDatabaseHas('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'cierre_pendiente',
            'estado_anterior' => EstadoPedidoCompra::RecibidoParcial->value,
            'estado_nuevo' => EstadoPedidoCompra::Cerrado->value,
            'descripcion' => 'Pedido cerrado con mercancia pendiente. Motivo: El proveedor no va a servir la cantidad pendiente.',
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_order_without_partial_reception_cannot_be_closed_with_pending_reason(): void
    {
        $this->seed(InventarioSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);
        $proveedor = Proveedor::query()->firstOrFail();
        $producto = $this->crearProductoPrueba();
        $pedido = $this->crearPedidoBorrador($usuario, $proveedor, $producto, 5);
        $pedido->update(['estado' => EstadoPedidoCompra::Pedido]);

        $this->actingAs($usuario)
            ->patch(route('admin.compras.pedidos.cerrar-pendiente', $pedido), [
                'motivo_cierre' => 'No deberia cerrar.',
            ])
            ->assertRedirect(route('admin.compras.pedidos.show', $pedido));

        $this->assertSame(EstadoPedidoCompra::Pedido, $pedido->refresh()->estado);
        $this->assertDatabaseMissing('eventos_pedido_compra', [
            'pedido_compra_id' => $pedido->id,
            'tipo' => 'cierre_pendiente',
        ]);
    }

    /**
     * @param array<string, mixed> $sobrescribir
     */
    private function crearProductoPrueba(array $sobrescribir = []): Producto
    {
        return Producto::query()->create(array_merge([
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
        ], $sobrescribir));
    }

    private function crearPedidoBorrador(Usuario $usuario, Proveedor $proveedor, Producto $producto, float $cantidad = 1, ?string $numero = null): PedidoCompra
    {
        $pedido = PedidoCompra::query()->create([
            'proveedor_id' => $proveedor->id,
            'numero' => $numero ?? 'PC-TEST-'.str()->random(6),
            'estado' => EstadoPedidoCompra::Borrador,
            'subtotal' => round($cantidad * 2, 2),
            'impuestos' => round($cantidad * 2 * 0.21, 2),
            'total' => round(($cantidad * 2) + ($cantidad * 2 * 0.21), 2),
            'creado_por' => $usuario->id,
            'actualizado_por' => $usuario->id,
        ]);

        $pedido->lineas()->create([
            'producto_id' => $producto->id,
            'descripcion' => $producto->nombre,
            'cantidad' => $cantidad,
            'coste_unitario' => 2,
            'iva_porcentaje' => 21,
            'subtotal' => round($cantidad * 2, 2),
            'impuestos' => round($cantidad * 2 * 0.21, 2),
            'total' => round(($cantidad * 2) + ($cantidad * 2 * 0.21), 2),
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
