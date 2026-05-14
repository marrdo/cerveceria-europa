<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Inventario\Enums\EstadoStockProducto;
use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\LoteInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\StockInventario;
use App\Modulos\Inventario\Services\DashboardInventarioMetricas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardInventarioController extends Controller
{
    /**
     * Muestra el panel operativo principal del modulo de inventario.
     */
    public function __invoke(DashboardInventarioMetricas $metricas): View
    {
        $productos = Producto::query()
            ->with(['categoria', 'proveedor', 'unidad', 'stock'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $productosConStock = $productos->where('controla_stock', true);
        $productosConEstado = $productosConStock
            ->map(function (Producto $producto): Producto {
                $producto->setAttribute('estado_stock_calculado', $producto->estadoStock());

                return $producto;
            });

        return view('modulos.inventario.dashboard', [
            'kpis' => [
                'productos_activos' => $productos->count(),
                'productos_con_existencias' => $productosConStock
                    ->filter(fn (Producto $producto): bool => $producto->cantidadStock() > 0)
                    ->count(),
                'productos_sin_stock' => $productosConEstado->where('estado_stock_calculado', EstadoStockProducto::SinStock)->count(),
                'productos_bajo_stock' => $productosConEstado->where('estado_stock_calculado', EstadoStockProducto::Bajo)->count(),
                'movimientos_hoy' => MovimientoInventario::query()->whereDate('created_at', today())->count(),
                'entradas_7_dias' => $this->sumarMovimientos(TipoMovimientoInventario::Entrada, 7),
                'salidas_7_dias' => $this->sumarMovimientos(TipoMovimientoInventario::Salida, 7),
                'valor_stock' => $this->valorEstimadoStock(),
            ],
            'productosSinStock' => $productosConEstado
                ->where('estado_stock_calculado', EstadoStockProducto::SinStock)
                ->take(6),
            'productosBajoStock' => $productosConEstado
                ->where('estado_stock_calculado', EstadoStockProducto::Bajo)
                ->take(6),
            'lotesCaducados' => $this->lotesCaducados(),
            'lotesProximosCaducar' => $this->lotesProximosCaducar(),
            'ultimosMovimientos' => MovimientoInventario::query()
                ->with(['producto.unidad', 'ubicacion', 'ubicacionOrigen', 'ubicacionDestino', 'creador'])
                ->latest('created_at')
                ->take(8)
                ->get(),
            'topSalidas' => $this->topProductosConSalidas(),
            'graficaEntradasSalidas' => $metricas->entradasSalidasPorDia(14),
            'graficaMovimientosPorTipo' => $metricas->movimientosPorTipo(30),
            'graficaSalidasPorCategoria' => $metricas->salidasPorCategoria(30),
            'graficaStockPorUbicacion' => $metricas->stockPorUbicacion(),
            'reposicionUrgente' => $metricas->reposicionUrgente(30),
            'stockSinMovimiento' => $metricas->stockSinMovimientoReciente(30),
        ]);
    }

    /**
     * Suma la cantidad movida de un tipo durante los ultimos dias.
     */
    private function sumarMovimientos(TipoMovimientoInventario $tipo, int $dias): float
    {
        return round((float) MovimientoInventario::query()
            ->where('tipo', $tipo->value)
            ->where('created_at', '>=', now()->subDays($dias))
            ->sum('cantidad'), 3);
    }

    /**
     * Calcula el valor teorico del stock usando el precio de coste de cada producto.
     */
    private function valorEstimadoStock(): float
    {
        return round((float) StockInventario::query()
            ->join('productos', 'productos.id', '=', 'stock_inventario.producto_id')
            ->where('productos.activo', true)
            ->where('productos.controla_stock', true)
            ->selectRaw('coalesce(sum(stock_inventario.cantidad * coalesce(productos.precio_coste, 0)), 0) as total')
            ->value('total'), 2);
    }

    /**
     * Devuelve los lotes caducados con stock disponible.
     *
     * @return Collection<int, LoteInventario>
     */
    private function lotesCaducados(): Collection
    {
        return $this->consultaLotesConCaducidad()
            ->whereDate('caduca_el', '<', now()->toDateString())
            ->take(6)
            ->get();
    }

    /**
     * Devuelve los lotes con caducidad en los proximos 30 dias.
     *
     * @return Collection<int, LoteInventario>
     */
    private function lotesProximosCaducar(): Collection
    {
        return $this->consultaLotesConCaducidad()
            ->whereDate('caduca_el', '>=', now()->toDateString())
            ->whereDate('caduca_el', '<=', now()->addDays(30)->toDateString())
            ->take(6)
            ->get();
    }

    /**
     * @return Builder<LoteInventario>
     */
    private function consultaLotesConCaducidad(): Builder
    {
        return LoteInventario::query()
            ->with(['producto.unidad', 'ubicacion'])
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->whereNotNull('caduca_el')
            ->orderBy('caduca_el')
            ->orderBy('created_at');
    }

    /**
     * Devuelve los productos con mas salidas registradas en los ultimos 30 dias.
     *
     * @return Collection<int, MovimientoInventario>
     */
    private function topProductosConSalidas(): Collection
    {
        return MovimientoInventario::query()
            ->select('producto_id')
            ->selectRaw('sum(cantidad) as cantidad_total')
            ->where('tipo', TipoMovimientoInventario::Salida->value)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('producto_id')
            ->groupBy('producto_id')
            ->orderByDesc(DB::raw('sum(cantidad)'))
            ->with('producto.unidad')
            ->take(6)
            ->get();
    }
}
