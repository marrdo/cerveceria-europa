<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Inventario\Enums\EstadoStockProducto;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\UbicacionInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InformeInventarioController extends Controller
{
    /**
     * Muestra alertas operativas de stock bajo y sin stock.
     */
    public function alertas(): View
    {
        $productos = $this->productosConEstadoAlerta();

        return view('modulos.inventario.informes.alertas', [
            'productosBajoStock' => $productos->where('estado_stock_calculado', EstadoStockProducto::Bajo),
            'productosSinStock' => $productos->where('estado_stock_calculado', EstadoStockProducto::SinStock),
        ]);
    }

    /**
     * Muestra informe filtrable de movimientos de inventario.
     */
    public function movimientos(Request $request): View
    {
        $filtros = $this->filtrosMovimientos($request);

        return view('modulos.inventario.informes.movimientos', [
            'movimientos' => $this->consultaMovimientos($filtros)->paginate(25)->withQueryString(),
            'productos' => Producto::query()->orderBy('nombre')->get(),
            'proveedores' => Proveedor::query()->orderBy('nombre')->get(),
            'ubicaciones' => UbicacionInventario::query()->orderBy('nombre')->get(),
            'tiposMovimiento' => TipoMovimientoInventario::cases(),
            'filtros' => $filtros,
        ]);
    }

    /**
     * Exporta productos en CSV UTF-8.
     */
    public function exportarProductos(): StreamedResponse
    {
        $productos = Producto::query()
            ->with(['categoria', 'proveedor', 'unidad', 'stock'])
            ->orderBy('nombre')
            ->get();

        return $this->csv('productos_inventario.csv', [
            ['Nombre', 'SKU', 'Categoria', 'Proveedor', 'Unidad', 'Stock', 'Estado', 'Precio venta', 'Precio coste', 'Activo'],
            ...$productos->map(fn (Producto $producto): array => [
                $producto->nombre,
                $producto->sku,
                $producto->categoria?->nombre,
                $producto->proveedor?->nombre,
                $producto->codigoUnidad(),
                $producto->formatearCantidad($producto->cantidadStock()),
                $producto->estadoStock()->etiqueta(),
                number_format((float) $producto->precio_venta, 2, ',', '.'),
                $producto->precio_coste === null ? '' : number_format((float) $producto->precio_coste, 2, ',', '.'),
                $producto->activo ? 'Si' : 'No',
            ])->all(),
        ]);
    }

    /**
     * Exporta movimientos filtrados en CSV UTF-8.
     */
    public function exportarMovimientos(Request $request): StreamedResponse
    {
        $movimientos = $this->consultaMovimientos($this->filtrosMovimientos($request))->get();

        return $this->csv('movimientos_inventario.csv', [
            ['Fecha', 'Usuario', 'Producto', 'Tipo', 'Cantidad', 'Unidad', 'Ubicacion', 'Origen', 'Destino', 'Proveedor', 'Motivo', 'Referencia', 'Stock antes', 'Stock despues'],
            ...$movimientos->map(fn (MovimientoInventario $movimiento): array => [
                $movimiento->created_at?->format('d/m/Y H:i'),
                $movimiento->creador?->nombre ?? 'Sin usuario',
                $movimiento->producto?->nombre,
                $movimiento->tipo->etiqueta(),
                $movimiento->producto?->formatearCantidad($movimiento->cantidad) ?? (string) $movimiento->cantidad,
                $movimiento->producto?->codigoUnidad(),
                $movimiento->ubicacion?->nombre,
                $movimiento->ubicacionOrigen?->nombre,
                $movimiento->ubicacionDestino?->nombre,
                $movimiento->proveedor?->nombre,
                $movimiento->motivo,
                $movimiento->referencia,
                $movimiento->producto?->formatearCantidad($movimiento->stock_antes) ?? (string) $movimiento->stock_antes,
                $movimiento->producto?->formatearCantidad($movimiento->stock_despues) ?? (string) $movimiento->stock_despues,
            ])->all(),
        ]);
    }

    /**
     * Exporta alertas de stock bajo y sin stock en CSV UTF-8.
     */
    public function exportarAlertas(): StreamedResponse
    {
        $productos = $this->productosConEstadoAlerta();

        return $this->csv('alertas_stock.csv', [
            ['Producto', 'SKU', 'Categoria', 'Proveedor', 'Stock', 'Unidad', 'Alerta', 'Estado'],
            ...$productos->map(fn (Producto $producto): array => [
                $producto->nombre,
                $producto->sku,
                $producto->categoria?->nombre,
                $producto->proveedor?->nombre,
                $producto->formatearCantidad($producto->cantidadStock()),
                $producto->codigoUnidad(),
                $producto->formatearCantidad($producto->cantidad_alerta_stock),
                $producto->estado_stock_calculado->etiqueta(),
            ])->all(),
        ]);
    }

    /**
     * Devuelve productos activos con estado bajo o sin stock.
     *
     * @return Collection<int, Producto>
     */
    private function productosConEstadoAlerta(): Collection
    {
        return Producto::query()
            ->with(['categoria', 'proveedor', 'unidad', 'stock'])
            ->where('activo', true)
            ->where('controla_stock', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Producto $producto): Producto {
                $producto->setAttribute('estado_stock_calculado', $producto->estadoStock());

                return $producto;
            })
            ->filter(fn (Producto $producto): bool => in_array($producto->estado_stock_calculado, [
                EstadoStockProducto::Bajo,
                EstadoStockProducto::SinStock,
            ], true))
            ->values();
    }

    /**
     * Construye los filtros seguros del informe de movimientos.
     *
     * @return array<string, string>
     */
    private function filtrosMovimientos(Request $request): array
    {
        return [
            'fecha_desde' => (string) $request->query('fecha_desde', ''),
            'fecha_hasta' => (string) $request->query('fecha_hasta', ''),
            'producto_id' => (string) $request->query('producto_id', ''),
            'proveedor_id' => (string) $request->query('proveedor_id', ''),
            'ubicacion_id' => (string) $request->query('ubicacion_id', ''),
            'tipo' => (string) $request->query('tipo', ''),
        ];
    }

    /**
     * Consulta base de movimientos con filtros operativos.
     *
     * @param array<string, string> $filtros
     * @return Builder<MovimientoInventario>
     */
    private function consultaMovimientos(array $filtros): Builder
    {
        return MovimientoInventario::query()
            ->with(['producto.unidad', 'proveedor', 'ubicacion', 'ubicacionOrigen', 'ubicacionDestino', 'creador'])
            ->when($filtros['fecha_desde'] !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filtros['fecha_desde']))
            ->when($filtros['fecha_hasta'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filtros['fecha_hasta']))
            ->when($filtros['producto_id'] !== '', fn (Builder $query) => $query->where('producto_id', $filtros['producto_id']))
            ->when($filtros['proveedor_id'] !== '', fn (Builder $query) => $query->where('proveedor_id', $filtros['proveedor_id']))
            ->when($filtros['tipo'] !== '', fn (Builder $query) => $query->where('tipo', $filtros['tipo']))
            ->when($filtros['ubicacion_id'] !== '', function (Builder $query) use ($filtros): void {
                $query->where(function (Builder $subquery) use ($filtros): void {
                    $subquery
                        ->where('ubicacion_inventario_id', $filtros['ubicacion_id'])
                        ->orWhere('ubicacion_origen_id', $filtros['ubicacion_id'])
                        ->orWhere('ubicacion_destino_id', $filtros['ubicacion_id']);
                });
            })
            ->latest('created_at');
    }

    /**
     * Genera una descarga CSV compatible con Excel en Windows.
     *
     * @param array<int, array<int, mixed>> $filas
     */
    private function csv(string $nombreArchivo, array $filas): StreamedResponse
    {
        return Response::streamDownload(function () use ($filas): void {
            $salida = fopen('php://output', 'wb');

            fwrite($salida, "\xEF\xBB\xBF");

            foreach ($filas as $fila) {
                fputcsv($salida, $fila, ';');
            }

            fclose($salida);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
