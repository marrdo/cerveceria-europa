<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Compras\Models\RecepcionCompra;
use App\Modulos\Espacios\Models\Mesa;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\Ventas\Models\LineaComanda;
use App\Modulos\Ventas\Models\PagoComanda;
use App\Modulos\Ventas\Models\TurnoCaja;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RecorridoDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecorridoDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_mysql_exige_innodb_para_transacciones_y_claves_foraneas(): void
    {
        $this->assertSame('InnoDB', config('database.connections.mysql.engine'));
    }

    public function test_crea_un_recorrido_comercial_conectado_entre_modulos(): void
    {
        $this->seed(DatabaseSeeder::class);

        $comandas = Comanda::query()
            ->where('numero', 'like', 'DEMO-COM-%')
            ->orderBy('numero')
            ->get()
            ->keyBy('numero');

        $this->assertCount(4, $comandas);
        $this->assertSame(EstadoComanda::Pagada, $comandas['DEMO-COM-001']->estado);
        $this->assertSame(EstadoComanda::Servida, $comandas['DEMO-COM-002']->estado);
        $this->assertSame(EstadoComanda::EnPreparacion, $comandas['DEMO-COM-003']->estado);
        $this->assertSame(EstadoComanda::Abierta, $comandas['DEMO-COM-004']->estado);
        $this->assertSame(4, LineaComanda::query()
            ->whereHas('comanda', fn ($query) => $query->where('numero', 'like', 'DEMO-COM-%'))
            ->whereNotNull('movimiento_inventario_id')
            ->count());

        $caja = TurnoCaja::query()->where('numero', 'DEMO-CAJA-ACTUAL')->sole();
        $pago = PagoComanda::query()->whereHas(
            'comanda',
            fn ($query) => $query->where('numero', 'DEMO-COM-001'),
        )->sole();
        $this->assertSame(EstadoTurnoCaja::Abierta, $caja->estado);
        $this->assertSame($caja->id, $pago->caja_turno_id);

        $pedidos = PedidoCompra::query()
            ->where('numero', 'like', 'DEMO-PC-%')
            ->orderBy('numero')
            ->get()
            ->keyBy('numero');
        $this->assertCount(2, $pedidos);
        $this->assertSame(EstadoPedidoCompra::Pedido, $pedidos['DEMO-PC-001']->estado);
        $this->assertSame(EstadoPedidoCompra::RecibidoParcial, $pedidos['DEMO-PC-002']->estado);
        $this->assertSame(1, RecepcionCompra::query()->where('numero', 'DEMO-RC-001')->count());

        $this->assertSame(8, Mesa::query()
            ->whereHas('zona', fn ($query) => $query->where('codigo', 'like', 'DEMO-%'))
            ->count());
        $this->assertStock('DEMO-CERV-001', 21);
        $this->assertStock('DEMO-REF-001', 16);
        $this->assertStock('DEMO-COC-001', 13);
    }

    public function test_el_recorrido_demo_es_idempotente(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(RecorridoDemoSeeder::class);

        $this->assertSame(4, Comanda::query()->where('numero', 'like', 'DEMO-COM-%')->count());
        $this->assertSame(1, TurnoCaja::query()->where('numero', 'like', 'DEMO-CAJA-%')->count());
        $this->assertSame(2, PedidoCompra::query()->where('numero', 'like', 'DEMO-PC-%')->count());
        $this->assertSame(1, RecepcionCompra::query()->where('numero', 'like', 'DEMO-RC-%')->count());
        $this->assertStock('DEMO-CERV-001', 21);
        $this->assertStock('DEMO-REF-001', 16);
        $this->assertStock('DEMO-COC-001', 13);
    }

    public function test_dashboard_guia_al_encargado_por_el_recorrido_preparado(): void
    {
        $this->seed(DatabaseSeeder::class);
        $encargado = Usuario::query()->where('rol', RolUsuario::Encargado)->firstOrFail();

        $this->actingAs($encargado)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Recorrido recomendado')
            ->assertSee('Carta pública')
            ->assertSee('Servicio en curso')
            ->assertSee('Inventario trazable')
            ->assertSee('Compra y reposición')
            ->assertSee('3 activas · 1 pagada');
    }

    private function assertStock(string $sku, float $esperado): void
    {
        $producto = Producto::query()->where('sku', $sku)->with('stock')->firstOrFail();

        $this->assertSame($esperado, round((float) $producto->stock->sum('cantidad'), 3));
    }
}
