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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Genera un inventario ficticio y variado para probar el panel.
 */
class InventarioDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $proveedor = Proveedor::query()->updateOrCreate(
                ['slug' => 'distribuciones-demo'],
                [
                    'nombre' => 'Distribuciones Demo',
                    'telefono' => '600 000 001',
                    'email' => 'pedidos@distribuciones.demo',
                    'activo' => true,
                ],
            );

            $productos = $this->crearProductos($proveedor);
            $this->limpiarDatosOperativos($productos);
            $this->crearStock($productos);
            $this->crearMovimientos($productos, $proveedor);
        });
    }

    /** @return Collection<string, Producto> */
    private function crearProductos(Proveedor $proveedor): Collection
    {
        return collect($this->productos())->mapWithKeys(function (array $datos) use ($proveedor): array {
            $categoria = CategoriaProducto::query()->where('slug', $datos['categoria'])->firstOrFail();
            $unidad = UnidadInventario::query()->where('codigo', $datos['unidad'])->firstOrFail();

            $producto = Producto::query()->updateOrCreate(
                ['sku' => $datos['sku']],
                [
                    'categoria_producto_id' => $categoria->id,
                    'proveedor_id' => $proveedor->id,
                    'unidad_inventario_id' => $unidad->id,
                    'nombre' => $datos['nombre'],
                    'referencia_proveedor' => $datos['referencia'],
                    'descripcion' => 'Producto ficticio creado exclusivamente para la demostración.',
                    'precio_venta' => $datos['venta'],
                    'precio_coste' => $datos['coste'],
                    'controla_stock' => true,
                    'controla_caducidad' => $datos['caducidad'],
                    'cantidad_alerta_stock' => $datos['alerta'],
                    'activo' => true,
                ],
            );

            return [$datos['sku'] => $producto];
        });
    }

    /** @param Collection<string, Producto> $productos */
    private function limpiarDatosOperativos(Collection $productos): void
    {
        $ids = $productos->pluck('id');

        MovimientoInventario::query()
            ->whereIn('producto_id', $ids)
            ->where('referencia', 'like', 'DEMO-INV-%')
            ->delete();

        StockInventario::query()->whereIn('producto_id', $ids)->delete();
    }

    /** @param Collection<string, Producto> $productos */
    private function crearStock(Collection $productos): void
    {
        $ubicaciones = UbicacionInventario::query()->get()->keyBy('codigo');

        foreach ($this->stocks() as $datos) {
            StockInventario::query()->create([
                'producto_id' => $productos[$datos['sku']]->id,
                'ubicacion_inventario_id' => $ubicaciones[$datos['ubicacion']]->id,
                'cantidad' => $datos['cantidad'],
                'cantidad_minima' => $datos['minimo'],
            ]);
        }
    }

    /** @param Collection<string, Producto> $productos */
    private function crearMovimientos(Collection $productos, Proveedor $proveedor): void
    {
        $ubicaciones = UbicacionInventario::query()->get()->keyBy('codigo');

        foreach ($this->movimientos() as $indice => $datos) {
            $producto = $productos[$datos['sku']];
            $fecha = now()->subDays($datos['dias'])->setTime(9 + ($indice % 10), 15);

            MovimientoInventario::query()->create([
                'producto_id' => $producto->id,
                'proveedor_id' => $proveedor->id,
                'ubicacion_inventario_id' => $ubicaciones[$datos['ubicacion']]->id,
                'tipo' => $datos['tipo'],
                'cantidad' => $datos['cantidad'],
                'stock_antes' => $datos['antes'],
                'stock_despues' => $datos['despues'],
                'coste_unitario' => $producto->precio_coste,
                'motivo' => $datos['motivo'],
                'referencia' => 'DEMO-INV-'.str_pad((string) ($indice + 1), 3, '0', STR_PAD_LEFT),
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function productos(): array
    {
        return [
            ['sku' => 'DEMO-REF-001', 'categoria' => 'refrescos', 'unidad' => 'botella', 'nombre' => 'Limonada de la casa', 'referencia' => 'REF-DEMO-001', 'venta' => 3.20, 'coste' => 0.85, 'alerta' => 8, 'caducidad' => false],
            ['sku' => 'DEMO-REF-002', 'categoria' => 'refrescos', 'unidad' => 'botella', 'nombre' => 'Refresco de cola Demo', 'referencia' => 'REF-DEMO-002', 'venta' => 2.80, 'coste' => 0.65, 'alerta' => 12, 'caducidad' => false],
            ['sku' => 'DEMO-CERV-001', 'categoria' => 'cervezas', 'unidad' => 'botella', 'nombre' => 'Cerveza rubia de la casa', 'referencia' => 'CERV-DEMO-001', 'venta' => 3.20, 'coste' => 0.95, 'alerta' => 12, 'caducidad' => false],
            ['sku' => 'DEMO-VIN-001', 'categoria' => 'vinos', 'unidad' => 'botella', 'nombre' => 'Vino blanco de la casa', 'referencia' => 'VIN-DEMO-001', 'venta' => 14.00, 'coste' => 5.20, 'alerta' => 4, 'caducidad' => false],
            ['sku' => 'DEMO-CAF-001', 'categoria' => 'cafes-e-infusiones', 'unidad' => 'kg', 'nombre' => 'Café en grano Demo', 'referencia' => 'CAF-DEMO-001', 'venta' => 24.00, 'coste' => 13.50, 'alerta' => 2, 'caducidad' => true],
            ['sku' => 'DEMO-COC-001', 'categoria' => 'alimentacion', 'unidad' => 'kg', 'nombre' => 'Patata para cocina', 'referencia' => 'COC-DEMO-001', 'venta' => 0, 'coste' => 1.20, 'alerta' => 10, 'caducidad' => true],
            ['sku' => 'DEMO-COC-002', 'categoria' => 'alimentacion', 'unidad' => 'l', 'nombre' => 'Aceite de cocina Demo', 'referencia' => 'COC-DEMO-002', 'venta' => 0, 'coste' => 6.80, 'alerta' => 5, 'caducidad' => true],
            ['sku' => 'DEMO-LIM-001', 'categoria' => 'limpieza', 'unidad' => 'l', 'nombre' => 'Lavavajillas profesional Demo', 'referencia' => 'LIM-DEMO-001', 'venta' => 0, 'coste' => 2.10, 'alerta' => 3, 'caducidad' => false],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function stocks(): array
    {
        return [
            ['sku' => 'DEMO-REF-001', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 18, 'minimo' => 8],
            ['sku' => 'DEMO-REF-002', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 6, 'minimo' => 12],
            ['sku' => 'DEMO-CERV-001', 'ubicacion' => 'CAMARA_FRIA', 'cantidad' => 24, 'minimo' => 12],
            ['sku' => 'DEMO-VIN-001', 'ubicacion' => 'ALMACEN', 'cantidad' => 0, 'minimo' => 4],
            ['sku' => 'DEMO-CAF-001', 'ubicacion' => 'BARRA', 'cantidad' => 3, 'minimo' => 2],
            ['sku' => 'DEMO-COC-001', 'ubicacion' => 'COCINA', 'cantidad' => 8, 'minimo' => 10],
            ['sku' => 'DEMO-COC-002', 'ubicacion' => 'COCINA', 'cantidad' => 7, 'minimo' => 5],
            ['sku' => 'DEMO-LIM-001', 'ubicacion' => 'ALMACEN', 'cantidad' => 2, 'minimo' => 3],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function movimientos(): array
    {
        return [
            ['sku' => 'DEMO-REF-001', 'ubicacion' => 'CAMARA_FRIA', 'tipo' => 'entrada', 'cantidad' => 36, 'antes' => 0, 'despues' => 36, 'dias' => 12, 'motivo' => 'Carga inicial de demostración'],
            ['sku' => 'DEMO-REF-001', 'ubicacion' => 'CAMARA_FRIA', 'tipo' => 'salida', 'cantidad' => 18, 'antes' => 36, 'despues' => 18, 'dias' => 2, 'motivo' => 'Consumo ficticio del servicio'],
            ['sku' => 'DEMO-REF-002', 'ubicacion' => 'CAMARA_FRIA', 'tipo' => 'entrada', 'cantidad' => 24, 'antes' => 0, 'despues' => 24, 'dias' => 10, 'motivo' => 'Recepción de prueba'],
            ['sku' => 'DEMO-REF-002', 'ubicacion' => 'CAMARA_FRIA', 'tipo' => 'salida', 'cantidad' => 18, 'antes' => 24, 'despues' => 6, 'dias' => 1, 'motivo' => 'Ventas ficticias'],
            ['sku' => 'DEMO-CERV-001', 'ubicacion' => 'CAMARA_FRIA', 'tipo' => 'entrada', 'cantidad' => 48, 'antes' => 0, 'despues' => 48, 'dias' => 9, 'motivo' => 'Carga inicial de demostración'],
            ['sku' => 'DEMO-CERV-001', 'ubicacion' => 'CAMARA_FRIA', 'tipo' => 'salida', 'cantidad' => 24, 'antes' => 48, 'despues' => 24, 'dias' => 1, 'motivo' => 'Servicio de fin de semana ficticio'],
            ['sku' => 'DEMO-VIN-001', 'ubicacion' => 'ALMACEN', 'tipo' => 'entrada', 'cantidad' => 6, 'antes' => 0, 'despues' => 6, 'dias' => 8, 'motivo' => 'Recepción de prueba'],
            ['sku' => 'DEMO-VIN-001', 'ubicacion' => 'ALMACEN', 'tipo' => 'salida', 'cantidad' => 6, 'antes' => 6, 'despues' => 0, 'dias' => 3, 'motivo' => 'Producto agotado en la demo'],
        ];
    }
}
