<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Modulos\Compras\Actions\CrearPedidoCompraBorradorAction;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Compras\Models\RecepcionCompra;
use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use App\Modulos\Espacios\Models\Mesa;
use App\Modulos\Espacios\Models\Recinto;
use App\Modulos\Espacios\Models\Zona;
use App\Modulos\Inventario\Actions\RegistrarMovimientoInventarioAction;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\LoteInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Ventas\Actions\AbrirTurnoCajaAction;
use App\Modulos\Ventas\Actions\CrearComandaAction;
use App\Modulos\Ventas\Actions\RegistrarPagoComandaAction;
use App\Modulos\Ventas\Actions\ServirLineaComandaAction;
use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\Ventas\Models\LineaComanda;
use App\Modulos\Ventas\Models\TurnoCaja;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Construye un recorrido comercial ficticio, conectado y repetible.
 *
 * Los prefijos DEMO permiten reconstruir el escenario sin tocar registros que
 * una persona haya creado manualmente durante sus pruebas.
 */
class RecorridoDemoSeeder extends Seeder
{
    private const MARCA_RECINTO = '[DEMO:RECINTO-PRINCIPAL]';

    public function run(): void
    {
        // Estas dependencias también hacen que ejecutar solo este seeder sea seguro.
        $this->call(InventarioSeeder::class);
        $this->call(WebPublicaSeeder::class);

        DB::transaction(function (): void {
            $this->limpiarOperativaAnterior();

            $usuarios = $this->usuariosDemo();
            $espacios = $this->crearEspacios();
            $caja = $this->crearCaja($espacios['recinto'], $usuarios['encargado']);

            $this->crearComandas($espacios, $usuarios, $caja);
            $this->crearCompras($usuarios['encargado']);
        });
    }

    /**
     * @return array{camarero: Usuario, encargado: Usuario, propietario: Usuario}
     */
    private function usuariosDemo(): array
    {
        $dominio = app()->isProduction() ? 'demo.invalid' : 'demo.local';

        return [
            'camarero' => Usuario::query()->where('email', "camarero@{$dominio}")->firstOrFail(),
            'encargado' => Usuario::query()->where('email', "encargado@{$dominio}")->firstOrFail(),
            'propietario' => Usuario::query()->where('email', "propietario@{$dominio}")->firstOrFail(),
        ];
    }

    /**
     * El inventario acaba de volver a su estado base. Solo se eliminan entidades
     * y trazas identificadas como parte del recorrido automático.
     */
    private function limpiarOperativaAnterior(): void
    {
        Comanda::withTrashed()->where('numero', 'like', 'DEMO-COM-%')->forceDelete();
        TurnoCaja::query()->where('numero', 'like', 'DEMO-CAJA-%')->delete();
        RecepcionCompra::query()->where('numero', 'like', 'DEMO-RC-%')->delete();
        PedidoCompra::withTrashed()->where('numero', 'like', 'DEMO-PC-%')->forceDelete();
        LoteInventario::withTrashed()->where('codigo_lote', 'like', 'DEMO-LOTE-%')->forceDelete();
        MovimientoInventario::query()
            ->where('referencia', 'like', 'DEMO-COM-%')
            ->orWhere('referencia', 'like', 'DEMO-RC-%')
            ->delete();
    }

    /**
     * @return array{recinto: Recinto, sala: Zona, terraza: Zona, mesas: Collection<string, Mesa>}
     */
    private function crearEspacios(): array
    {
        $negocio = ConfiguracionNegocio::actual();
        $recinto = Recinto::withTrashed()->firstOrNew(['notas' => self::MARCA_RECINTO]);
        $recinto->fill([
            'nombre_comercial' => $negocio->nombre_comercial,
            'nombre_fiscal' => $negocio->razon_social,
            'direccion' => $negocio->direccion,
            'localidad' => $negocio->localidad,
            'provincia' => $negocio->provincia,
            'codigo_postal' => $negocio->codigo_postal,
            'pais' => $negocio->pais ?: 'España',
            'telefono' => $negocio->telefono,
            'email' => $negocio->email,
            'activo' => true,
        ]);
        $recinto->forceFill(['deleted_at' => null])->save();

        $sala = $this->actualizarZona($recinto, 'DEMO-SALA', 'Sala y barra', 10);
        $terraza = $this->actualizarZona($recinto, 'DEMO-TERRAZA', 'Terraza', 20);
        $mesas = collect();

        foreach (range(1, 5) as $numero) {
            $mesa = $this->actualizarMesa($sala, 'Mesa '.$numero, $numero === 5 ? 6 : 4, $numero);
            $mesas->put('mesa-'.$numero, $mesa);
        }

        foreach (range(1, 3) as $numero) {
            $mesa = $this->actualizarMesa($terraza, 'Terraza '.$numero, 4, $numero);
            $mesas->put('terraza-'.$numero, $mesa);
        }

        return compact('recinto', 'sala', 'terraza', 'mesas');
    }

    private function actualizarZona(Recinto $recinto, string $codigo, string $nombre, int $orden): Zona
    {
        $zona = Zona::withTrashed()->firstOrNew([
            'recinto_id' => $recinto->id,
            'codigo' => $codigo,
        ]);
        $zona->fill([
            'nombre' => $nombre,
            'orden' => $orden,
            'notas' => 'Zona ficticia del recorrido comercial.',
            'activa' => true,
        ]);
        $zona->forceFill(['deleted_at' => null])->save();

        return $zona;
    }

    private function actualizarMesa(Zona $zona, string $nombre, int $capacidad, int $orden): Mesa
    {
        $mesa = Mesa::withTrashed()->firstOrNew([
            'zona_id' => $zona->id,
            'nombre' => $nombre,
        ]);
        $mesa->fill([
            'capacidad' => $capacidad,
            'orden' => $orden,
            'notas' => 'Mesa ficticia del recorrido comercial.',
            'activa' => true,
        ]);
        $mesa->forceFill(['deleted_at' => null])->save();

        return $mesa;
    }

    private function crearCaja(Recinto $recinto, Usuario $encargado): TurnoCaja
    {
        $caja = app(AbrirTurnoCajaAction::class)->execute([
            'recinto_id' => $recinto->id,
            'saldo_inicial' => 100,
            'notas_apertura' => '[DEMO] Caja abierta para probar cobros y cierre.',
        ], $encargado->id);
        $abiertaAt = now()->startOfDay()->addHours(9);
        $caja->forceFill([
            'numero' => 'DEMO-CAJA-ACTUAL',
            'abierta_at' => $abiertaAt,
            'created_at' => $abiertaAt,
            'updated_at' => $abiertaAt,
        ])->save();

        return $caja;
    }

    /**
     * @param  array{recinto: Recinto, sala: Zona, terraza: Zona, mesas: Collection<string, Mesa>}  $espacios
     * @param  array{camarero: Usuario, encargado: Usuario, propietario: Usuario}  $usuarios
     */
    private function crearComandas(array $espacios, array $usuarios, TurnoCaja $caja): void
    {
        $contenidos = ContenidoWeb::query()
            ->with('tarifas')
            ->whereIn('slug', [
                'patatas-bravas-demo',
                'croquetas-cremosas-demo',
                'tarta-de-queso-demo',
                'rubia-de-la-casa-demo',
                'limonada-casera-demo',
                'cafe-de-la-casa-demo',
            ])
            ->get()
            ->keyBy('slug');
        $camara = UbicacionInventario::query()->where('codigo', 'CAMARA_FRIA')->firstOrFail();
        $crear = app(CrearComandaAction::class);
        $servir = app(ServirLineaComandaAction::class);
        $cobrar = app(RegistrarPagoComandaAction::class);

        $pagada = $crear->execute([
            ...$this->datosEspacio($espacios, 'mesa-1'),
            'ubicacion_inventario_id' => $camara->id,
            'cliente_nombre' => 'Mesa de demostración',
            'notas' => '[DEMO] Venta terminada: carta, servicio, stock y pago.',
            'lineas' => [
                $this->lineaCarta($contenidos, 'patatas-bravas-demo', 1),
                $this->lineaCarta($contenidos, 'rubia-de-la-casa-demo', 2, 'Caña'),
                $this->lineaCarta($contenidos, 'limonada-casera-demo', 1),
            ],
        ], $usuarios['camarero']->id);
        $pagada->update(['numero' => 'DEMO-COM-001']);
        $this->servirTodas($pagada, $servir, $usuarios['camarero']);
        $pago = $cobrar->execute($pagada->refresh(), [
            'metodo' => MetodoPagoComanda::Tarjeta,
            'importe' => (float) $pagada->total,
            'referencia' => 'DEMO-TPV-001',
            'notas' => 'Pago ficticio completo.',
        ], $usuarios['encargado']->id);
        $this->fecharComanda($pagada->refresh(), now()->subHours(2), now()->subMinutes(75), now()->subMinutes(70));
        $pago->forceFill([
            'caja_turno_id' => $caja->id,
            'cobrado_at' => now()->subMinutes(70),
            'created_at' => now()->subMinutes(70),
            'updated_at' => now()->subMinutes(70),
        ])->save();

        $servida = $crear->execute([
            ...$this->datosEspacio($espacios, 'terraza-1'),
            'ubicacion_inventario_id' => $camara->id,
            'cliente_nombre' => 'Terraza pendiente de cobro',
            'notas' => '[DEMO] Pedido servido listo para registrar el cobro.',
            'lineas' => [
                $this->lineaCarta($contenidos, 'croquetas-cremosas-demo', 1),
                $this->lineaCarta($contenidos, 'rubia-de-la-casa-demo', 1, 'Pinta'),
            ],
        ], $usuarios['camarero']->id);
        $servida->update(['numero' => 'DEMO-COM-002']);
        $this->servirTodas($servida, $servir, $usuarios['camarero']);
        $this->fecharComanda($servida->refresh(), now()->subMinutes(45), now()->subMinutes(20));

        $preparacion = $crear->execute([
            ...$this->datosEspacio($espacios, 'mesa-3'),
            'ubicacion_inventario_id' => $camara->id,
            'cliente_nombre' => 'Servicio en curso',
            'notas' => '[DEMO] Una línea servida y otra aún en preparación.',
            'lineas' => [
                $this->lineaCarta($contenidos, 'croquetas-cremosas-demo', 1),
                $this->lineaCarta($contenidos, 'limonada-casera-demo', 1),
            ],
        ], $usuarios['camarero']->id);
        $preparacion->update(['numero' => 'DEMO-COM-003']);
        $lineaLimonada = $preparacion->lineas->first(
            fn (LineaComanda $linea): bool => $linea->contenidoWeb?->slug === 'limonada-casera-demo',
        ) ?? $preparacion->lineas->last();
        $servir->execute($lineaLimonada, $usuarios['camarero']->id);
        $this->fecharComanda($preparacion->refresh(), now()->subMinutes(18), now()->subMinutes(8));

        $abierta = $crear->execute([
            ...$this->datosEspacio($espacios, 'mesa-5'),
            'ubicacion_inventario_id' => $camara->id,
            'cliente_nombre' => 'Nueva mesa',
            'notas' => '[DEMO] Comanda recién tomada para iniciar el recorrido.',
            'lineas' => [
                $this->lineaCarta($contenidos, 'tarta-de-queso-demo', 2),
                $this->lineaCarta($contenidos, 'cafe-de-la-casa-demo', 2),
            ],
        ], $usuarios['camarero']->id);
        $abierta->forceFill([
            'numero' => 'DEMO-COM-004',
            'created_at' => now()->subMinutes(3),
            'updated_at' => now()->subMinutes(3),
        ])->save();
    }

    /**
     * @param  array{recinto: Recinto, sala: Zona, terraza: Zona, mesas: Collection<string, Mesa>}  $espacios
     * @return array<string, string|null>
     */
    private function datosEspacio(array $espacios, string $claveMesa): array
    {
        /** @var Mesa $mesa */
        $mesa = $espacios['mesas']->get($claveMesa);
        $zona = $mesa->zona;

        return [
            'mesa' => $mesa->nombre,
            'recinto_id' => $espacios['recinto']->id,
            'zona_id' => $zona->id,
            'mesa_id' => $mesa->id,
        ];
    }

    /**
     * @param  Collection<string, ContenidoWeb>  $contenidos
     * @return array<string, mixed>
     */
    private function lineaCarta(Collection $contenidos, string $slug, float $cantidad, ?string $tarifa = null): array
    {
        /** @var ContenidoWeb $contenido */
        $contenido = $contenidos->get($slug) ?? throw new \RuntimeException("Falta el contenido demo {$slug}.");
        $tarifaId = $tarifa === null
            ? null
            : $contenido->tarifas->firstWhere('nombre', $tarifa)?->id;

        return [
            'contenido_web_id' => $contenido->id,
            'tarifa_contenido_web_id' => $tarifaId,
            'cantidad' => $cantidad,
            'notas' => null,
        ];
    }

    private function servirTodas(Comanda $comanda, ServirLineaComandaAction $servir, Usuario $usuario): void
    {
        foreach ($comanda->lineas as $linea) {
            $servir->execute($linea, $usuario->id);
        }
    }

    private function fecharComanda(
        Comanda $comanda,
        Carbon $creadaAt,
        ?Carbon $servidaAt = null,
        ?Carbon $cerradaAt = null,
    ): void {
        $comanda->forceFill([
            'servida_at' => $servidaAt,
            'cerrada_at' => $cerradaAt,
            'created_at' => $creadaAt,
            'updated_at' => $cerradaAt ?? $servidaAt ?? $creadaAt,
        ])->save();
        $comanda->lineas()->update([
            'created_at' => $creadaAt,
            'updated_at' => $servidaAt ?? $creadaAt,
        ]);

        foreach ($comanda->lineas()->whereNotNull('movimiento_inventario_id')->with('movimientoInventario')->get() as $linea) {
            $linea->movimientoInventario?->forceFill([
                'created_at' => $servidaAt ?? $creadaAt,
                'updated_at' => $servidaAt ?? $creadaAt,
            ])->save();
        }
    }

    private function crearCompras(Usuario $encargado): void
    {
        $productos = Producto::query()
            ->whereIn('sku', ['DEMO-REF-002', 'DEMO-VIN-001', 'DEMO-COC-001', 'DEMO-COC-002'])
            ->get()
            ->keyBy('sku');
        $proveedor = Proveedor::query()->where('slug', 'distribuciones-demo')->firstOrFail();
        $crearPedido = app(CrearPedidoCompraBorradorAction::class);

        $pendiente = $crearPedido->execute([
            'proveedor_id' => $proveedor->id,
            'fecha_pedido' => now()->toDateString(),
            'fecha_prevista' => now()->addDay()->toDateString(),
            'notas' => '[DEMO] Reposición propuesta para referencias con stock bajo.',
        ], [
            $this->lineaCompra($productos['DEMO-REF-002'], 24),
            $this->lineaCompra($productos['DEMO-VIN-001'], 6),
        ], $encargado->id);
        $pendiente->update([
            'numero' => 'DEMO-PC-001',
            'estado' => EstadoPedidoCompra::Pedido,
        ]);
        $pendiente->eventos()->create([
            'tipo' => 'estado',
            'estado_anterior' => EstadoPedidoCompra::Borrador->value,
            'estado_nuevo' => EstadoPedidoCompra::Pedido->value,
            'descripcion' => 'Pedido demo enviado al proveedor y pendiente de recepción.',
            'usuario_id' => $encargado->id,
        ]);

        $parcial = $crearPedido->execute([
            'proveedor_id' => $proveedor->id,
            'fecha_pedido' => now()->subDays(2)->toDateString(),
            'fecha_prevista' => now()->toDateString(),
            'notas' => '[DEMO] Pedido con una recepción parcial para continuar la prueba.',
        ], [
            $this->lineaCompra($productos['DEMO-COC-001'], 10, 10),
            $this->lineaCompra($productos['DEMO-COC-002'], 4, 21),
        ], $encargado->id);
        $parcial->update([
            'numero' => 'DEMO-PC-002',
            'estado' => EstadoPedidoCompra::Pedido,
        ]);
        $this->crearRecepcionParcial($parcial, $productos['DEMO-COC-001'], $proveedor, $encargado);
    }

    /** @return array<string, mixed> */
    private function lineaCompra(Producto $producto, float $cantidad, float $iva = 21): array
    {
        return [
            'producto_id' => $producto->id,
            'descripcion' => $producto->nombre,
            'cantidad' => $cantidad,
            'coste_unitario' => (float) $producto->precio_coste,
            'iva_porcentaje' => $iva,
        ];
    }

    private function crearRecepcionParcial(
        PedidoCompra $pedido,
        Producto $producto,
        Proveedor $proveedor,
        Usuario $encargado,
    ): void {
        $ubicacion = UbicacionInventario::query()->where('codigo', 'COCINA')->firstOrFail();
        $lineaPedido = $pedido->lineas()->where('producto_id', $producto->id)->firstOrFail();
        $recepcion = RecepcionCompra::query()->create([
            'pedido_compra_id' => $pedido->id,
            'numero' => 'DEMO-RC-001',
            'fecha_recepcion' => now()->subDay()->toDateString(),
            'notas' => '[DEMO] Llegó solo la primera referencia; queda mercancía pendiente.',
            'recibido_por' => $encargado->id,
        ]);
        $movimiento = app(RegistrarMovimientoInventarioAction::class)->execute($producto, [
            'tipo' => TipoMovimientoInventario::Entrada->value,
            'proveedor_id' => $proveedor->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => 5,
            'coste_unitario' => (float) $lineaPedido->coste_unitario,
            'motivo' => 'Recepción parcial del pedido '.$pedido->numero,
            'referencia' => $recepcion->numero,
            'codigo_lote' => 'DEMO-LOTE-PATATA-001',
            'caduca_el' => now()->addDays(20)->toDateString(),
            'notas' => 'Entrada ficticia conectada al recorrido demo.',
        ], $encargado->id);

        $recepcion->lineas()->create([
            'linea_pedido_compra_id' => $lineaPedido->id,
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'movimiento_inventario_id' => $movimiento->id,
            'cantidad' => 5,
            'coste_unitario' => $lineaPedido->coste_unitario,
            'codigo_lote' => 'DEMO-LOTE-PATATA-001',
            'caduca_el' => now()->addDays(20)->toDateString(),
            'notas' => 'Recepción parcial ficticia.',
        ]);
        $pedido->update(['estado' => EstadoPedidoCompra::RecibidoParcial]);
        $pedido->eventos()->create([
            'tipo' => 'recepcion',
            'estado_anterior' => EstadoPedidoCompra::Pedido->value,
            'estado_nuevo' => EstadoPedidoCompra::RecibidoParcial->value,
            'descripcion' => 'Recepción parcial registrada: '.$recepcion->numero.'.',
            'usuario_id' => $encargado->id,
        ]);
        $fecha = now()->subDay()->setTime(10, 30);
        $recepcion->forceFill(['created_at' => $fecha, 'updated_at' => $fecha])->save();
        $movimiento->forceFill(['created_at' => $fecha, 'updated_at' => $fecha])->save();
    }
}
