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
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Models\LineaComanda;
use App\Modulos\WebPublica\Models\ContenidoWeb;
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
        $comparativaVentasStock = $this->comparativaVentasStock(limite: 500);
        $resumenEconomicoVentasInventario = $this->resumenEconomicoVentasInventario($comparativaVentasStock);

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
                'carta_sin_inventario' => $this->contadorCartaSinControlInventario(),
                'ventas_sin_descuento_stock' => $this->contadorVentasServidasSinDescuentoStock(),
                'margen_bruto_30_dias' => $resumenEconomicoVentasInventario['margen_bruto'],
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
            'contenidosCartaSinInventario' => $this->contenidosCartaSinControlInventario(),
            'lineasServidasSinProducto' => $this->lineasServidasSinProductoInventario(),
            'lineasInventariablesSinMovimiento' => $this->lineasInventariablesSinMovimiento(),
            'comparativaVentasStock' => $comparativaVentasStock->take(8)->values(),
            'resumenEconomicoVentasInventario' => $resumenEconomicoVentasInventario,
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

    /**
     * Cuenta contenidos de carta publicados que no pueden descontar stock.
     */
    private function contadorCartaSinControlInventario(): int
    {
        return $this->consultaCartaSinControlInventario()->count();
    }

    /**
     * Cuenta lineas servidas que no han generado salida de inventario.
     */
    private function contadorVentasServidasSinDescuentoStock(): int
    {
        return $this->consultaLineasServidasSinProductoInventario()->count()
            + $this->consultaLineasInventariablesSinMovimiento()->count();
    }

    /**
     * Devuelve contenidos publicados sin producto inventariable asociado.
     *
     * @return Collection<int, ContenidoWeb>
     */
    private function contenidosCartaSinControlInventario(): Collection
    {
        return $this->consultaCartaSinControlInventario()
            ->with(['categoriaCarta.padre', 'producto.unidad'])
            ->orderBy('orden')
            ->orderBy('titulo')
            ->take(8)
            ->get();
    }

    /**
     * Devuelve lineas servidas que no tenian producto de inventario asociado.
     *
     * @return Collection<int, LineaComanda>
     */
    private function lineasServidasSinProductoInventario(): Collection
    {
        return $this->consultaLineasServidasSinProductoInventario()
            ->with(['comanda', 'contenidoWeb'])
            ->latest('servida_at')
            ->take(6)
            ->get();
    }

    /**
     * Devuelve lineas inventariables servidas sin movimiento de salida.
     *
     * @return Collection<int, LineaComanda>
     */
    private function lineasInventariablesSinMovimiento(): Collection
    {
        return $this->consultaLineasInventariablesSinMovimiento()
            ->with(['comanda', 'producto.unidad', 'contenidoWeb'])
            ->latest('servida_at')
            ->take(6)
            ->get();
    }

    /**
     * Query base de contenidos publicados que no controlan inventario.
     *
     * @return Builder<ContenidoWeb>
     */
    private function consultaCartaSinControlInventario(): Builder
    {
        return ContenidoWeb::query()
            ->where('publicado', true)
            ->where(function (Builder $query): void {
                $query->whereNull('publicado_desde')->orWhereDate('publicado_desde', '<=', now()->toDateString());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('publicado_hasta')->orWhereDate('publicado_hasta', '>=', now()->toDateString());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('producto_id')
                    ->orWhereHas('producto', fn (Builder $productoQuery) => $productoQuery->where('controla_stock', false));
            });
    }

    /**
     * Query base de lineas servidas sin producto de inventario.
     *
     * @return Builder<LineaComanda>
     */
    private function consultaLineasServidasSinProductoInventario(): Builder
    {
        return LineaComanda::query()
            ->where('estado', EstadoLineaComanda::Servida->value)
            ->whereNull('producto_id')
            ->where('servida_at', '>=', now()->subDays(30));
    }

    /**
     * Query base de lineas de productos inventariables servidas sin movimiento.
     *
     * @return Builder<LineaComanda>
     */
    private function consultaLineasInventariablesSinMovimiento(): Builder
    {
        return LineaComanda::query()
            ->where('estado', EstadoLineaComanda::Servida->value)
            ->whereNull('movimiento_inventario_id')
            ->where('servida_at', '>=', now()->subDays(30))
            ->whereHas('producto', fn (Builder $productoQuery) => $productoQuery->where('controla_stock', true));
    }

    /**
     * Compara ventas servidas contra salidas reales generadas desde esas ventas.
     *
     * @return Collection<int, array{
     *     producto: Producto,
     *     cantidad_vendida: float,
     *     cantidad_descontada: float,
     *     diferencia: float,
     *     ingresos: float,
     *     coste_estimado: float,
     *     margen_bruto: float,
     *     margen_porcentaje: float|null
     * }>
     */
    private function comparativaVentasStock(int $dias = 30, int $limite = 8): Collection
    {
        return $this->lineasInventariablesServidas($dias)
            ->groupBy('producto_id')
            ->map(function (Collection $lineas): array {
                /** @var Producto $producto */
                $producto = $lineas->first()->producto;
                $cantidadVendida = round((float) $lineas->sum('cantidad'), 3);
                $cantidadDescontada = round((float) $lineas->sum(fn (LineaComanda $linea): float => (float) ($linea->movimientoInventario?->cantidad ?? 0)), 3);
                $ingresos = round((float) $lineas->sum('total'), 2);
                $costeEstimado = round($cantidadVendida * (float) ($producto->precio_coste ?? 0), 2);
                $margenBruto = round($ingresos - $costeEstimado, 2);

                return [
                    'producto' => $producto,
                    'cantidad_vendida' => $cantidadVendida,
                    'cantidad_descontada' => $cantidadDescontada,
                    'diferencia' => round($cantidadVendida - $cantidadDescontada, 3),
                    'ingresos' => $ingresos,
                    'coste_estimado' => $costeEstimado,
                    'margen_bruto' => $margenBruto,
                    'margen_porcentaje' => $ingresos > 0 ? round(($margenBruto / $ingresos) * 100, 2) : null,
                ];
            })
            ->sortByDesc(fn (array $fila): float => abs((float) $fila['diferencia']))
            ->take($limite)
            ->values();
    }

    /**
     * Resume ingresos, coste y margen estimado de ventas inventariables.
     *
     * @return array{
     *     ingresos: float,
     *     coste_estimado: float,
     *     margen_bruto: float,
     *     margen_porcentaje: float|null,
     *     unidades_vendidas: float,
     *     unidades_descontadas: float,
     *     productos_con_descuadre: int
     * }
     */
    private function resumenEconomicoVentasInventario(?Collection $comparativa = null, int $dias = 30): array
    {
        $comparativa ??= $this->comparativaVentasStock($dias, 500);
        $ingresos = round((float) $comparativa->sum('ingresos'), 2);
        $costeEstimado = round((float) $comparativa->sum('coste_estimado'), 2);
        $margenBruto = round($ingresos - $costeEstimado, 2);

        return [
            'ingresos' => $ingresos,
            'coste_estimado' => $costeEstimado,
            'margen_bruto' => $margenBruto,
            'margen_porcentaje' => $ingresos > 0 ? round(($margenBruto / $ingresos) * 100, 2) : null,
            'unidades_vendidas' => round((float) $comparativa->sum('cantidad_vendida'), 3),
            'unidades_descontadas' => round((float) $comparativa->sum('cantidad_descontada'), 3),
            'productos_con_descuadre' => $comparativa
                ->filter(fn (array $fila): bool => abs((float) $fila['diferencia']) > 0.001)
                ->count(),
        ];
    }

    /**
     * Lineas servidas con producto inventariable en un periodo reciente.
     *
     * @return Collection<int, LineaComanda>
     */
    private function lineasInventariablesServidas(int $dias): Collection
    {
        return LineaComanda::query()
            ->with(['producto.categoria', 'producto.unidad', 'movimientoInventario'])
            ->where('estado', EstadoLineaComanda::Servida->value)
            ->where('servida_at', '>=', now()->subDays(max(1, $dias)))
            ->whereHas('producto', fn (Builder $productoQuery) => $productoQuery->where('controla_stock', true))
            ->get();
    }
}
