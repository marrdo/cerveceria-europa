<?php

namespace App\Modulos\Inventario\Services;

use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Models\LineaComanda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VentasInventarioMetricas
{
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
     *     margen_porcentaje: float|null,
     *     incidencias: int
     * }>
     */
    public function comparativaVentasStock(int $dias = 30, int $limite = 8): Collection
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
                    'incidencias' => $this->contarIncidencias($lineas),
                ];
            })
            ->sortByDesc(fn (array $fila): float => abs((float) $fila['diferencia']))
            ->take($limite)
            ->values();
    }

    /**
     * Resume ingresos, coste y margen estimado de ventas inventariables.
     *
     * @param Collection<int, array<string, mixed>>|null $comparativa
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
    public function resumenEconomico(?Collection $comparativa = null, int $dias = 30): array
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
     * Detecta productos con incidencias repetidas entre ventas servidas y salidas de stock.
     *
     * @return Collection<int, array{
     *     producto: Producto,
     *     incidencias: int,
     *     diferencia_total: float,
     *     primera_incidencia: string|null,
     *     ultima_incidencia: string|null
     * }>
     */
    public function descuadresRepetidos(int $dias = 30, int $limite = 6, int $minimoIncidencias = 2): Collection
    {
        return $this->lineasInventariablesServidas($dias)
            ->filter(fn (LineaComanda $linea): bool => abs($this->diferenciaLinea($linea)) > 0.001)
            ->groupBy('producto_id')
            ->map(function (Collection $lineas): array {
                /** @var Producto $producto */
                $producto = $lineas->first()->producto;

                return [
                    'producto' => $producto,
                    'incidencias' => $lineas->count(),
                    'diferencia_total' => round((float) $lineas->sum(fn (LineaComanda $linea): float => $this->diferenciaLinea($linea)), 3),
                    'primera_incidencia' => $lineas->min('servida_at')?->format('d/m/Y H:i'),
                    'ultima_incidencia' => $lineas->max('servida_at')?->format('d/m/Y H:i'),
                ];
            })
            ->filter(fn (array $fila): bool => $fila['incidencias'] >= $minimoIncidencias)
            ->sortByDesc('incidencias')
            ->take($limite)
            ->values();
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

    /**
     * Cuenta lineas cuya cantidad servida no coincide con la salida de inventario.
     *
     * @param Collection<int, LineaComanda> $lineas
     */
    private function contarIncidencias(Collection $lineas): int
    {
        return $lineas
            ->filter(fn (LineaComanda $linea): bool => abs($this->diferenciaLinea($linea)) > 0.001)
            ->count();
    }

    /**
     * Calcula diferencia entre cantidad servida y cantidad descontada.
     */
    private function diferenciaLinea(LineaComanda $linea): float
    {
        return round((float) $linea->cantidad - (float) ($linea->movimientoInventario?->cantidad ?? 0), 3);
    }
}
