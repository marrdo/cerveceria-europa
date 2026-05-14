<?php

namespace App\Modulos\Inventario\Services;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\StockInventario;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardInventarioMetricas
{
    /**
     * Construye la serie diaria de entradas y salidas para el dashboard.
     *
     * @return Collection<int, array{
     *     fecha: string,
     *     etiqueta: string,
     *     entradas: float,
     *     salidas: float,
     *     total: float,
     *     porcentaje_entradas: float,
     *     porcentaje_salidas: float
     * }>
     */
    public function entradasSalidasPorDia(int $dias = 14): Collection
    {
        $inicio = CarbonImmutable::today()->subDays(max(1, $dias) - 1);
        $movimientos = MovimientoInventario::query()
            ->whereIn('tipo', [
                TipoMovimientoInventario::Entrada->value,
                TipoMovimientoInventario::Salida->value,
            ])
            ->whereDate('created_at', '>=', $inicio->toDateString())
            ->get(['tipo', 'cantidad', 'created_at'])
            ->groupBy(fn (MovimientoInventario $movimiento): string => $movimiento->created_at?->toDateString() ?? '');

        $serie = collect(range(0, max(1, $dias) - 1))->map(function (int $offset) use ($inicio, $movimientos): array {
            $fecha = $inicio->addDays($offset);
            $movimientosDia = $movimientos->get($fecha->toDateString(), collect());
            $entradas = round((float) $movimientosDia
                ->filter(fn (MovimientoInventario $movimiento): bool => $movimiento->tipo === TipoMovimientoInventario::Entrada)
                ->sum('cantidad'), 3);
            $salidas = round((float) $movimientosDia
                ->filter(fn (MovimientoInventario $movimiento): bool => $movimiento->tipo === TipoMovimientoInventario::Salida)
                ->sum('cantidad'), 3);

            return [
                'fecha' => $fecha->toDateString(),
                'etiqueta' => $fecha->format('d/m'),
                'entradas' => $entradas,
                'salidas' => $salidas,
                'total' => max($entradas, $salidas),
                'porcentaje_entradas' => 0.0,
                'porcentaje_salidas' => 0.0,
            ];
        });

        $maximo = max(1, (float) $serie->max('total'));

        return $serie->map(fn (array $dia): array => array_merge($dia, [
            'porcentaje_entradas' => round(((float) $dia['entradas'] / $maximo) * 100, 2),
            'porcentaje_salidas' => round(((float) $dia['salidas'] / $maximo) * 100, 2),
        ]));
    }

    /**
     * Resume los movimientos por tipo en un periodo reciente.
     *
     * @return Collection<int, array{
     *     tipo: string,
     *     etiqueta: string,
     *     cantidad: float,
     *     movimientos: int,
     *     porcentaje: float,
     *     variant: string
     * }>
     */
    public function movimientosPorTipo(int $dias = 30): Collection
    {
        $desde = now()->subDays(max(1, $dias));
        $movimientos = MovimientoInventario::query()
            ->where('created_at', '>=', $desde)
            ->get(['tipo', 'cantidad'])
            ->groupBy(fn (MovimientoInventario $movimiento): string => $movimiento->tipo->value);

        $serie = collect(TipoMovimientoInventario::cases())->map(function (TipoMovimientoInventario $tipo) use ($movimientos): array {
            $grupo = $movimientos->get($tipo->value, collect());

            return [
                'tipo' => $tipo->value,
                'etiqueta' => $tipo->etiqueta(),
                'cantidad' => round((float) $grupo->sum('cantidad'), 3),
                'movimientos' => $grupo->count(),
                'porcentaje' => 0.0,
                'variant' => match ($tipo) {
                    TipoMovimientoInventario::Entrada => 'success',
                    TipoMovimientoInventario::Salida => 'danger',
                    TipoMovimientoInventario::Ajuste => 'warning',
                    TipoMovimientoInventario::Transferencia => 'info',
                },
            ];
        });

        $maximo = max(1, (float) $serie->max('cantidad'));

        return $serie->map(fn (array $tipo): array => array_merge($tipo, [
            'porcentaje' => round(((float) $tipo['cantidad'] / $maximo) * 100, 2),
        ]));
    }

    /**
     * Agrupa las salidas recientes por categoria de producto.
     *
     * @return Collection<int, array{
     *     categoria: string,
     *     cantidad: float,
     *     movimientos: int,
     *     porcentaje: float
     * }>
     */
    public function salidasPorCategoria(int $dias = 30, int $limite = 6): Collection
    {
        $movimientos = MovimientoInventario::query()
            ->with('producto.categoria')
            ->where('tipo', TipoMovimientoInventario::Salida->value)
            ->where('created_at', '>=', now()->subDays(max(1, $dias)))
            ->get(['producto_id', 'tipo', 'cantidad', 'created_at'])
            ->groupBy(fn (MovimientoInventario $movimiento): string => $movimiento->producto?->categoria?->nombre ?? 'Sin categoria');

        $serie = $movimientos
            ->map(fn (Collection $grupo, string $categoria): array => [
                'categoria' => $categoria,
                'cantidad' => round((float) $grupo->sum('cantidad'), 3),
                'movimientos' => $grupo->count(),
                'porcentaje' => 0.0,
            ])
            ->sortByDesc('cantidad')
            ->take($limite)
            ->values();

        $maximo = max(1, (float) $serie->max('cantidad'));

        return $serie->map(fn (array $categoria): array => array_merge($categoria, [
            'porcentaje' => round(((float) $categoria['cantidad'] / $maximo) * 100, 2),
        ]));
    }

    /**
     * Agrupa el stock disponible por ubicacion operativa.
     *
     * @return Collection<int, array{
     *     ubicacion: string,
     *     ubicacion_id: string,
     *     cantidad: float,
     *     lineas: int,
     *     porcentaje: float
     * }>
     */
    public function stockPorUbicacion(int $limite = 6): Collection
    {
        $stock = StockInventario::query()
            ->with('ubicacion')
            ->where('cantidad', '>', 0)
            ->get(['ubicacion_inventario_id', 'cantidad'])
            ->groupBy(fn (StockInventario $stock): string => $stock->ubicacion?->nombre ?? 'Sin ubicacion');

        $serie = $stock
            ->map(fn (Collection $grupo, string $ubicacion): array => [
                'ubicacion' => $ubicacion,
                'ubicacion_id' => (string) $grupo->first()?->ubicacion_inventario_id,
                'cantidad' => round((float) $grupo->sum('cantidad'), 3),
                'lineas' => $grupo->count(),
                'porcentaje' => 0.0,
            ])
            ->sortByDesc('cantidad')
            ->take($limite)
            ->values();

        $maximo = max(1, (float) $serie->max('cantidad'));

        return $serie->map(fn (array $ubicacion): array => array_merge($ubicacion, [
            'porcentaje' => round(((float) $ubicacion['cantidad'] / $maximo) * 100, 2),
        ]));
    }

}
