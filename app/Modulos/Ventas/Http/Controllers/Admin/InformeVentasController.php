<?php

namespace App\Modulos\Ventas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\Ventas\Models\LineaComanda;
use App\Modulos\Ventas\Models\PagoComanda;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InformeVentasController extends Controller
{
    /**
     * Muestra el cuadro de informes comerciales del modulo de ventas.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeConsultarInformesVentas(), 403);

        $filtros = $this->filtros($request);

        return view('modulos.ventas.informes.index', [
            'filtros' => $filtros,
            'kpis' => $this->kpis($filtros),
            'ventasPorDia' => $this->ventasPorDia($filtros),
            'ventasPorMetodo' => $this->ventasPorMetodo($filtros),
            'ventasPorCamarero' => $this->ventasPorCamarero($filtros),
            'productosMasVendidos' => $this->productosMasVendidos($filtros),
            'ventasPorCategoria' => $this->ventasPorCategoria($filtros),
            'comandasCanceladas' => $this->comandasCanceladas($filtros),
        ]);
    }

    /**
     * Normaliza filtros de fecha. Por defecto muestra el mes actual.
     *
     * @return array{fecha_desde: string, fecha_hasta: string, desde: Carbon, hasta: Carbon}
     */
    private function filtros(Request $request): array
    {
        $fechaDesde = (string) $request->query('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = (string) $request->query('fecha_hasta', now()->toDateString());

        $desde = Carbon::parse($fechaDesde)->startOfDay();
        $hasta = Carbon::parse($fechaHasta)->endOfDay();

        if ($desde->greaterThan($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
            [$fechaDesde, $fechaHasta] = [$desde->toDateString(), $hasta->toDateString()];
        }

        return [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'desde' => $desde,
            'hasta' => $hasta,
        ];
    }

    /**
     * Calcula KPIs principales del periodo.
     *
     * @param array{desde: Carbon, hasta: Carbon} $filtros
     * @return array<string, float|int>
     */
    private function kpis(array $filtros): array
    {
        $pagos = $this->consultaPagosCobrados($filtros)->get(['importe']);
        $comandasPagadas = $this->consultaComandasPagadas($filtros)->get(['id', 'total']);
        $canceladas = $this->consultaComandasCanceladas($filtros)->count();
        $totalVentas = round((float) $pagos->sum('importe'), 2);
        $totalComandas = $comandasPagadas->count();

        return [
            'total_ventas' => $totalVentas,
            'comandas_pagadas' => $totalComandas,
            'ticket_medio' => $totalComandas > 0 ? round($totalVentas / $totalComandas, 2) : 0,
            'comandas_canceladas' => $canceladas,
        ];
    }

    /**
     * Ventas cobradas agrupadas por dia.
     *
     * @param array{desde: Carbon, hasta: Carbon} $filtros
     * @return Collection<int, object>
     */
    private function ventasPorDia(array $filtros): Collection
    {
        return $this->consultaPagosCobrados($filtros)
            ->selectRaw('DATE(cobrado_at) as fecha, SUM(importe) as total, COUNT(*) as pagos')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    /**
     * Ventas cobradas por metodo de pago.
     *
     * @param array{desde: Carbon, hasta: Carbon} $filtros
     * @return Collection<int, object>
     */
    private function ventasPorMetodo(array $filtros): Collection
    {
        return $this->consultaPagosCobrados($filtros)
            ->selectRaw('metodo, SUM(importe) as total, COUNT(*) as pagos')
            ->groupBy('metodo')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Ventas cobradas por usuario que registra el pago.
     *
     * @param array{desde: Carbon, hasta: Carbon} $filtros
     * @return Collection<int, object>
     */
    private function ventasPorCamarero(array $filtros): Collection
    {
        return $this->consultaPagosCobrados($filtros)
            ->leftJoin('usuarios', 'usuarios.id', '=', 'pagos_comanda.cobrado_por')
            ->selectRaw("COALESCE(usuarios.nombre, 'Sin usuario') as usuario, SUM(pagos_comanda.importe) as total, COUNT(*) as pagos")
            ->groupBy('usuario')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Productos de carta mas vendidos en comandas pagadas.
     *
     * @param array{desde: Carbon, hasta: Carbon} $filtros
     * @return Collection<int, object>
     */
    private function productosMasVendidos(array $filtros): Collection
    {
        return $this->consultaLineasVendidas($filtros)
            ->selectRaw('lineas_comanda.nombre, SUM(lineas_comanda.cantidad) as cantidad, SUM(lineas_comanda.total) as total')
            ->groupBy('lineas_comanda.nombre')
            ->orderByDesc('cantidad')
            ->limit(10)
            ->get();
    }

    /**
     * Ventas por categoria de carta en comandas pagadas.
     *
     * @param array{desde: Carbon, hasta: Carbon} $filtros
     * @return Collection<int, object>
     */
    private function ventasPorCategoria(array $filtros): Collection
    {
        return $this->consultaLineasVendidas($filtros)
            ->leftJoin('contenidos_web', 'contenidos_web.id', '=', 'lineas_comanda.contenido_web_id')
            ->leftJoin('categorias_carta', 'categorias_carta.id', '=', 'contenidos_web.categoria_carta_id')
            ->leftJoin('categorias_carta as padres', 'padres.id', '=', 'categorias_carta.categoria_padre_id')
            ->selectRaw("COALESCE(padres.nombre, categorias_carta.nombre, 'Sin categoria') as categoria, SUM(lineas_comanda.total) as total, SUM(lineas_comanda.cantidad) as cantidad")
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Ultimas comandas canceladas del periodo.
     *
     * @param array{desde: Carbon, hasta: Carbon} $filtros
     * @return Collection<int, Comanda>
     */
    private function comandasCanceladas(array $filtros): Collection
    {
        return $this->consultaComandasCanceladas($filtros)
            ->with(['creador', 'zona', 'mesaEspacio'])
            ->latest('cerrada_at')
            ->limit(10)
            ->get();
    }

    /**
     * Consulta base de pagos cobrados dentro del periodo.
     */
    private function consultaPagosCobrados(array $filtros)
    {
        return PagoComanda::query()
            ->whereBetween('cobrado_at', [$filtros['desde'], $filtros['hasta']]);
    }

    /**
     * Consulta comandas pagadas dentro del periodo.
     */
    private function consultaComandasPagadas(array $filtros)
    {
        return Comanda::query()
            ->where('estado', EstadoComanda::Pagada->value)
            ->whereBetween('cerrada_at', [$filtros['desde'], $filtros['hasta']]);
    }

    /**
     * Consulta lineas vendidas de comandas pagadas dentro del periodo.
     */
    private function consultaLineasVendidas(array $filtros)
    {
        return LineaComanda::query()
            ->join('comandas', 'comandas.id', '=', 'lineas_comanda.comanda_id')
            ->where('comandas.estado', EstadoComanda::Pagada->value)
            ->where('lineas_comanda.estado', '!=', EstadoLineaComanda::Cancelada->value)
            ->whereBetween('comandas.cerrada_at', [$filtros['desde'], $filtros['hasta']]);
    }

    /**
     * Consulta comandas canceladas dentro del periodo.
     */
    private function consultaComandasCanceladas(array $filtros)
    {
        return Comanda::query()
            ->where('estado', EstadoComanda::Cancelada->value)
            ->whereBetween(DB::raw('COALESCE(cerrada_at, updated_at)'), [$filtros['desde'], $filtros['hasta']]);
    }
}
