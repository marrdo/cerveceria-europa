<?php

namespace Database\Seeders;

use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\StockInventario;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class InventarioCervezasDemoSeeder extends Seeder
{
    /**
     * Importa las cervezas reales extraidas de Numier y genera stock/movimientos demo.
     *
     * El objetivo no es simular contabilidad perfecta, sino dejar datos suficientes para
     * validar dashboard, alertas, productos sin stock y graficas de movimientos.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $categoria = CategoriaProducto::query()->updateOrCreate(
                ['slug' => 'cervezas'],
                ['nombre' => 'Cervezas', 'descripcion' => null, 'activo' => true],
            );
            $proveedor = Proveedor::query()->updateOrCreate(
                ['slug' => 'distribuidor-cervezas-europa'],
                ['nombre' => 'Distribuidor Cervezas Europa', 'telefono' => null, 'email' => null, 'activo' => true],
            );

            $unidades = [
                'botella' => UnidadInventario::query()->where('codigo', 'botella')->firstOrFail(),
                'barril' => UnidadInventario::query()->where('codigo', 'barril')->firstOrFail(),
            ];

            $ubicaciones = [
                'ALMACEN' => UbicacionInventario::query()->where('codigo', 'ALMACEN')->firstOrFail(),
                'CAMARA_FRIA' => UbicacionInventario::query()->where('codigo', 'CAMARA_FRIA')->firstOrFail(),
                'BARRA' => UbicacionInventario::query()->where('codigo', 'BARRA')->firstOrFail(),
            ];

            $productos = $this->importarProductos($categoria, $proveedor, $unidades);

            $this->limpiarDatosDemo($productos);
            $this->crearStocksDemo($productos, $ubicaciones);
            $this->crearMovimientosDemo($productos, $proveedor, $ubicaciones);
        });
    }

    /**
     * Crea o actualiza todos los productos de cerveza presentes en `Cartasdesdenumier.txt`.
     *
     * @param array<string, UnidadInventario> $unidades
     * @return \Illuminate\Support\Collection<string, Producto>
     */
    private function importarProductos(CategoriaProducto $categoria, Proveedor $proveedor, array $unidades): \Illuminate\Support\Collection
    {
        return collect($this->productosDesdeNumier())
            ->mapWithKeys(function (array $datos) use ($categoria, $proveedor, $unidades): array {
                $precioVenta = $datos['precio_venta'];
                $sku = 'NUMIER-CERV-'.$datos['id_numier'];

                $producto = Producto::query()->updateOrCreate(
                    ['sku' => $sku],
                    [
                        'categoria_producto_id' => $categoria->id,
                        'proveedor_id' => $proveedor->id,
                        'unidad_inventario_id' => $unidades[$datos['unidad']]->id,
                        'nombre' => Str::limit($datos['nombre'], 190, ''),
                        'codigo_barras' => null,
                        'referencia_proveedor' => 'NUMIER-'.$datos['id_numier'],
                        'descripcion' => $this->descripcionInventario($datos),
                        'precio_venta' => $precioVenta,
                        'precio_coste' => $precioVenta !== null ? round($precioVenta * 0.42, 2) : null,
                        'controla_stock' => true,
                        'controla_caducidad' => false,
                        'cantidad_alerta_stock' => $datos['unidad'] === 'barril' ? 1 : 6,
                        'activo' => true,
                    ],
                );

                return [$sku => $producto];
            });
    }

    /**
     * Elimina solo stock y movimientos generados por este seeder para poder relanzarlo.
     *
     * @param \Illuminate\Support\Collection<string, Producto> $productos
     */
    private function limpiarDatosDemo(\Illuminate\Support\Collection $productos): void
    {
        $ids = $productos->pluck('id');

        MovimientoInventario::query()
            ->whereIn('producto_id', $ids)
            ->where('referencia', 'like', 'DEMO-CARTA-%')
            ->delete();

        StockInventario::query()
            ->whereIn('producto_id', $ids)
            ->delete();
    }

    /**
     * Deja una foto final de stock con productos llenos, bajos y agotados.
     *
     * @param \Illuminate\Support\Collection<string, Producto> $productos
     * @param array<string, UbicacionInventario> $ubicaciones
     */
    private function crearStocksDemo(\Illuminate\Support\Collection $productos, array $ubicaciones): void
    {
        foreach ($this->stocksDemo() as $stock) {
            if (! $productos->has($stock['sku'])) {
                continue;
            }

            StockInventario::query()->create([
                'producto_id' => $productos[$stock['sku']]->id,
                'ubicacion_inventario_id' => $ubicaciones[$stock['ubicacion']]->id,
                'cantidad' => $stock['cantidad'],
                'cantidad_minima' => $stock['minimo'],
            ]);
        }
    }

    /**
     * Crea actividad historica para alimentar las graficas del dashboard.
     *
     * @param \Illuminate\Support\Collection<string, Producto> $productos
     * @param array<string, UbicacionInventario> $ubicaciones
     */
    private function crearMovimientosDemo(\Illuminate\Support\Collection $productos, Proveedor $proveedor, array $ubicaciones): void
    {
        foreach ($this->movimientosDemo() as $movimiento) {
            if (! $productos->has($movimiento['sku'])) {
                continue;
            }

            $fecha = now()->subDays($movimiento['dias'])->setTime(10 + ($movimiento['dias'] % 9), 15);
            $producto = $productos[$movimiento['sku']];
            $ubicacion = $movimiento['ubicacion'] ?? null;
            $origen = $movimiento['origen'] ?? null;
            $destino = $movimiento['destino'] ?? null;

            MovimientoInventario::query()->create([
                'producto_id' => $producto->id,
                'proveedor_id' => $proveedor->id,
                'ubicacion_inventario_id' => $ubicacion ? $ubicaciones[$ubicacion]->id : null,
                'ubicacion_origen_id' => $origen ? $ubicaciones[$origen]->id : null,
                'ubicacion_destino_id' => $destino ? $ubicaciones[$destino]->id : null,
                'tipo' => $movimiento['tipo'],
                'cantidad' => $movimiento['cantidad'],
                'stock_antes' => $movimiento['stock_antes'],
                'stock_despues' => $movimiento['stock_despues'],
                'coste_unitario' => $producto->precio_coste,
                'motivo' => $movimiento['motivo'],
                'referencia' => 'DEMO-CARTA-'.$movimiento['referencia'],
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);
        }
    }

    /**
     * Lee el bloque de Cervezas del fichero exportado desde Numier.
     *
     * @return array<int, array<string, mixed>>
     */
    private function productosDesdeNumier(): array
    {
        $ruta = database_path('../Cartasdesdenumier.txt');

        if (! File::exists($ruta)) {
            throw new RuntimeException('No se ha encontrado Cartasdesdenumier.txt en la raiz del proyecto.');
        }

        $contenido = File::get($ruta);
        preg_match_all('/^([^\r\n{}][^\r\n]*)\R(\{.*?)(?=^\S[^\r\n{}]*\R\{|\\z)/msu', $contenido, $coincidencias, PREG_SET_ORDER);

        return collect($coincidencias)
            ->map(fn (array $coincidencia): array => json_decode($coincidencia[2], true, 512, JSON_THROW_ON_ERROR))
            ->flatMap(function (array $payload): array {
                return $payload['data']['content'] ?? [];
            })
            ->filter(fn (array $categoria): bool => Str::upper((string) ($categoria['nombrePadre'] ?? '')) === 'CERVEZAS')
            ->flatMap(function (array $categoria): array {
                return collect($categoria['products'] ?? [])
                    ->map(fn (array $producto): array => $this->productoDesdeNumier($producto, $categoria))
                    ->all();
            })
            ->unique('id_numier')
            ->values()
            ->all();
    }

    /**
     * Normaliza un producto de Numier al formato de inventario demo.
     *
     * @param array<string, mixed> $producto
     * @param array<string, mixed> $categoria
     * @return array<string, mixed>
     */
    private function productoDesdeNumier(array $producto, array $categoria): array
    {
        $rates = collect($producto['rates'] ?? [])
            ->filter(fn (array $rate): bool => isset($rate['price']) && is_numeric($rate['price']))
            ->values();

        return [
            'id_numier' => (int) ($producto['id'] ?? 0),
            'nombre' => trim((string) ($producto['name'] ?? 'Cerveza sin nombre')),
            'descripcion' => trim((string) ($producto['description'] ?? '')),
            'categoria_numier' => trim((string) ($categoria['name'] ?? 'Cervezas')),
            'unidad' => Str::contains(Str::upper((string) ($categoria['name'] ?? '')), 'BARRIL') ? 'barril' : 'botella',
            'precio_venta' => $rates->isNotEmpty() ? (float) $rates->first()['price'] : null,
        ];
    }

    /**
     * Conserva la categoria de Numier dentro de la descripcion interna.
     *
     * @param array<string, mixed> $datos
     */
    private function descripcionInventario(array $datos): ?string
    {
        $partes = array_filter([
            'Categoria Numier: '.$datos['categoria_numier'],
            $datos['descripcion'] ?: null,
        ]);

        return $partes === [] ? null : implode(' · ', $partes);
    }

    /**
     * @return array<int, array{sku: string, ubicacion: string, cantidad: float, minimo: float}>
     */
    private function stocksDemo(): array
    {
        return [
            ['sku' => 'NUMIER-CERV-253409', 'ubicacion' => 'ALMACEN', 'cantidad' => 48, 'minimo' => 12],
            ['sku' => 'NUMIER-CERV-253409', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 18, 'minimo' => 6],
            ['sku' => 'NUMIER-CERV-253406', 'ubicacion' => 'ALMACEN', 'cantidad' => 0, 'minimo' => 12],
            ['sku' => 'NUMIER-CERV-253393', 'ubicacion' => 'BARRA', 'cantidad' => 1, 'minimo' => 1],
            ['sku' => 'NUMIER-CERV-253393', 'ubicacion' => 'ALMACEN', 'cantidad' => 2, 'minimo' => 1],
            ['sku' => 'NUMIER-CERV-253388', 'ubicacion' => 'BARRA', 'cantidad' => 1, 'minimo' => 1],
            ['sku' => 'NUMIER-CERV-386003', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 7, 'minimo' => 4],
            ['sku' => 'NUMIER-CERV-365642', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 2, 'minimo' => 2],
            ['sku' => 'NUMIER-CERV-277337', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 9, 'minimo' => 10],
            ['sku' => 'NUMIER-CERV-390611', 'ubicacion' => 'ALMACEN', 'cantidad' => 36, 'minimo' => 24],
            ['sku' => 'NUMIER-CERV-390611', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 12, 'minimo' => 6],
            ['sku' => 'NUMIER-CERV-259421', 'ubicacion' => 'ALMACEN', 'cantidad' => 0, 'minimo' => 12],
            ['sku' => 'NUMIER-CERV-347444', 'ubicacion' => 'BARRA', 'cantidad' => 0, 'minimo' => 1],
            ['sku' => 'NUMIER-CERV-407047', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 3, 'minimo' => 3],
            ['sku' => 'NUMIER-CERV-408379', 'ubicacion' => 'ALMACEN', 'cantidad' => 6, 'minimo' => 8],
            ['sku' => 'NUMIER-CERV-356388', 'ubicacion' => 'ALMACEN', 'cantidad' => 4, 'minimo' => 2],
            ['sku' => 'NUMIER-CERV-415045', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 0, 'minimo' => 4],
            ['sku' => 'NUMIER-CERV-253483', 'ubicacion' => 'BARRA', 'cantidad' => 1, 'minimo' => 1],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function movimientosDemo(): array
    {
        return [
            ['sku' => 'NUMIER-CERV-253409', 'tipo' => 'entrada', 'ubicacion' => 'ALMACEN', 'cantidad' => 96, 'stock_antes' => 0, 'stock_despues' => 96, 'dias' => 13, 'motivo' => 'Pedido inicial Leffe Rubia', 'referencia' => 'ENT-001'],
            ['sku' => 'NUMIER-CERV-253409', 'tipo' => 'transferencia', 'origen' => 'ALMACEN', 'destino' => 'CAMARA_FRIA', 'cantidad' => 24, 'stock_antes' => 72, 'stock_despues' => 48, 'dias' => 8, 'motivo' => 'Reposicion de camara', 'referencia' => 'TRF-001'],
            ['sku' => 'NUMIER-CERV-253409', 'tipo' => 'salida', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 12, 'stock_antes' => 30, 'stock_despues' => 18, 'dias' => 1, 'motivo' => 'Ventas servidas', 'referencia' => 'SAL-001'],
            ['sku' => 'NUMIER-CERV-253406', 'tipo' => 'entrada', 'ubicacion' => 'ALMACEN', 'cantidad' => 24, 'stock_antes' => 0, 'stock_despues' => 24, 'dias' => 12, 'motivo' => 'Pedido inicial Leffe Roja', 'referencia' => 'ENT-002'],
            ['sku' => 'NUMIER-CERV-253406', 'tipo' => 'salida', 'ubicacion' => 'ALMACEN', 'cantidad' => 24, 'stock_antes' => 24, 'stock_despues' => 0, 'dias' => 3, 'motivo' => 'Ventas y rotacion completa', 'referencia' => 'SAL-002'],
            ['sku' => 'NUMIER-CERV-253393', 'tipo' => 'entrada', 'ubicacion' => 'ALMACEN', 'cantidad' => 4, 'stock_antes' => 0, 'stock_despues' => 4, 'dias' => 10, 'motivo' => 'Recepcion barriles Hoegaarden', 'referencia' => 'ENT-003'],
            ['sku' => 'NUMIER-CERV-253393', 'tipo' => 'transferencia', 'origen' => 'ALMACEN', 'destino' => 'BARRA', 'cantidad' => 1, 'stock_antes' => 3, 'stock_despues' => 2, 'dias' => 6, 'motivo' => 'Cambio de barril a barra', 'referencia' => 'TRF-002'],
            ['sku' => 'NUMIER-CERV-253393', 'tipo' => 'salida', 'ubicacion' => 'BARRA', 'cantidad' => 1, 'stock_antes' => 2, 'stock_despues' => 1, 'dias' => 0, 'motivo' => 'Barril consumido en servicio', 'referencia' => 'SAL-003'],
            ['sku' => 'NUMIER-CERV-253388', 'tipo' => 'entrada', 'ubicacion' => 'ALMACEN', 'cantidad' => 2, 'stock_antes' => 0, 'stock_despues' => 2, 'dias' => 8, 'motivo' => 'Recepcion Franziskaner', 'referencia' => 'ENT-004'],
            ['sku' => 'NUMIER-CERV-253388', 'tipo' => 'transferencia', 'origen' => 'ALMACEN', 'destino' => 'BARRA', 'cantidad' => 1, 'stock_antes' => 2, 'stock_despues' => 1, 'dias' => 4, 'motivo' => 'Montaje en tirador', 'referencia' => 'TRF-003'],
            ['sku' => 'NUMIER-CERV-386003', 'tipo' => 'entrada', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 12, 'stock_antes' => 0, 'stock_despues' => 12, 'dias' => 11, 'motivo' => 'Recepcion gourmet', 'referencia' => 'ENT-005'],
            ['sku' => 'NUMIER-CERV-386003', 'tipo' => 'salida', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 5, 'stock_antes' => 12, 'stock_despues' => 7, 'dias' => 5, 'motivo' => 'Ventas gourmet', 'referencia' => 'SAL-004'],
            ['sku' => 'NUMIER-CERV-365642', 'tipo' => 'entrada', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 6, 'stock_antes' => 0, 'stock_despues' => 6, 'dias' => 9, 'motivo' => 'Recepcion 75cl', 'referencia' => 'ENT-006'],
            ['sku' => 'NUMIER-CERV-365642', 'tipo' => 'salida', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 4, 'stock_antes' => 6, 'stock_despues' => 2, 'dias' => 2, 'motivo' => 'Ventas botella 75cl', 'referencia' => 'SAL-005'],
            ['sku' => 'NUMIER-CERV-277337', 'tipo' => 'entrada', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 18, 'stock_antes' => 0, 'stock_despues' => 18, 'dias' => 7, 'motivo' => 'Recepcion Grimbergen', 'referencia' => 'ENT-007'],
            ['sku' => 'NUMIER-CERV-277337', 'tipo' => 'salida', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 9, 'stock_antes' => 18, 'stock_despues' => 9, 'dias' => 1, 'motivo' => 'Ventas destacadas', 'referencia' => 'SAL-006'],
            ['sku' => 'NUMIER-CERV-390611', 'tipo' => 'entrada', 'ubicacion' => 'ALMACEN', 'cantidad' => 72, 'stock_antes' => 0, 'stock_despues' => 72, 'dias' => 14, 'motivo' => 'Compra Alhambra', 'referencia' => 'ENT-008'],
            ['sku' => 'NUMIER-CERV-390611', 'tipo' => 'salida', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 24, 'stock_antes' => 36, 'stock_despues' => 12, 'dias' => 0, 'motivo' => 'Servicio fin de semana', 'referencia' => 'SAL-007'],
            ['sku' => 'NUMIER-CERV-259421', 'tipo' => 'entrada', 'ubicacion' => 'ALMACEN', 'cantidad' => 12, 'stock_antes' => 0, 'stock_despues' => 12, 'dias' => 6, 'motivo' => 'Compra sin alcohol', 'referencia' => 'ENT-009'],
            ['sku' => 'NUMIER-CERV-259421', 'tipo' => 'salida', 'ubicacion' => 'ALMACEN', 'cantidad' => 12, 'stock_antes' => 12, 'stock_despues' => 0, 'dias' => 1, 'motivo' => 'Ventas sin alcohol', 'referencia' => 'SAL-008'],
            ['sku' => 'NUMIER-CERV-347444', 'tipo' => 'entrada', 'ubicacion' => 'BARRA', 'cantidad' => 1, 'stock_antes' => 0, 'stock_despues' => 1, 'dias' => 5, 'motivo' => 'Barril invitado', 'referencia' => 'ENT-010'],
            ['sku' => 'NUMIER-CERV-347444', 'tipo' => 'salida', 'ubicacion' => 'BARRA', 'cantidad' => 1, 'stock_antes' => 1, 'stock_despues' => 0, 'dias' => 0, 'motivo' => 'Barril agotado', 'referencia' => 'SAL-009'],
            ['sku' => 'NUMIER-CERV-407047', 'tipo' => 'ajuste', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 1, 'stock_antes' => 4, 'stock_despues' => 3, 'dias' => 3, 'motivo' => 'Rotura detectada', 'referencia' => 'AJU-001'],
            ['sku' => 'NUMIER-CERV-408379', 'tipo' => 'entrada', 'ubicacion' => 'ALMACEN', 'cantidad' => 12, 'stock_antes' => 0, 'stock_despues' => 12, 'dias' => 9, 'motivo' => 'Recepcion sin gluten', 'referencia' => 'ENT-011'],
            ['sku' => 'NUMIER-CERV-408379', 'tipo' => 'salida', 'ubicacion' => 'ALMACEN', 'cantidad' => 6, 'stock_antes' => 12, 'stock_despues' => 6, 'dias' => 2, 'motivo' => 'Ventas sin gluten', 'referencia' => 'SAL-010'],
        ];
    }
}
