<?php

namespace App\Modulos\Inventario\Services;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
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

    /**
     * Calcula productos que deberian revisarse para reposicion.
     *
     * La regla combina stock bajo, productos sin stock y dias estimados restantes
     * segun salidas recientes. Es una metrica orientativa para decidir, no una orden
     * automatica de compra.
     *
     * @return Collection<int, array{
     *     producto: Producto,
     *     stock_actual: float,
     *     salidas_periodo: float,
     *     consumo_medio_diario: float,
     *     dias_restantes: float|null,
     *     motivo: string,
     *     urgencia: int
     * }>
     */
    public function reposicionUrgente(int $dias = 30, int $limite = 8, int $umbralDias = 7): Collection
    {
        $salidas = $this->salidasPorProducto($dias);

        return Producto::query()
            ->with(['categoria', 'proveedor', 'unidad', 'stock'])
            ->where('activo', true)
            ->where('controla_stock', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Producto $producto) use ($salidas, $dias, $umbralDias): array {
                $stockActual = $producto->cantidadStock();
                $salidasPeriodo = round((float) ($salidas[$producto->id]['cantidad'] ?? 0), 3);
                $consumoMedioDiario = $salidasPeriodo > 0 ? round($salidasPeriodo / max(1, $dias), 3) : 0.0;
                $diasRestantes = $consumoMedioDiario > 0 ? round($stockActual / $consumoMedioDiario, 1) : null;
                $alerta = (float) $producto->cantidad_alerta_stock;

                [$motivo, $urgencia] = match (true) {
                    $stockActual <= 0 => ['Sin stock disponible', 0],
                    $alerta > 0 && $stockActual <= $alerta => ['Por debajo del minimo', 1],
                    $diasRestantes !== null && $diasRestantes <= $umbralDias => ['Se agota pronto', 2],
                    default => ['Stock suficiente', 9],
                };

                return [
                    'producto' => $producto,
                    'stock_actual' => $stockActual,
                    'salidas_periodo' => $salidasPeriodo,
                    'consumo_medio_diario' => $consumoMedioDiario,
                    'dias_restantes' => $diasRestantes,
                    'motivo' => $motivo,
                    'urgencia' => $urgencia,
                ];
            })
            ->filter(fn (array $fila): bool => $fila['urgencia'] < 9)
            ->sortBy([
                ['urgencia', 'asc'],
                ['dias_restantes', 'asc'],
                ['salidas_periodo', 'desc'],
            ])
            ->take($limite)
            ->values();
    }

    /**
     * Detecta productos con stock disponible pero sin movimientos recientes.
     *
     * @return Collection<int, array{
     *     producto: Producto,
     *     stock_actual: float,
     *     ultimo_movimiento: string|null,
     *     dias_sin_movimiento: int|null
     * }>
     */
    public function stockSinMovimientoReciente(int $dias = 30, int $limite = 8): Collection
    {
        $ultimoMovimiento = MovimientoInventario::query()
            ->select('producto_id')
            ->selectRaw('max(created_at) as ultimo_movimiento')
            ->whereNotNull('producto_id')
            ->groupBy('producto_id')
            ->get()
            ->keyBy('producto_id');

        return Producto::query()
            ->with(['categoria', 'proveedor', 'unidad', 'stock'])
            ->where('activo', true)
            ->where('controla_stock', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (Producto $producto) use ($ultimoMovimiento): array {
                $stockActual = $producto->cantidadStock();
                $ultimo = $ultimoMovimiento[$producto->id]['ultimo_movimiento'] ?? null;
                $diasSinMovimiento = $ultimo ? now()->diffInDays($ultimo) : null;

                return [
                    'producto' => $producto,
                    'stock_actual' => $stockActual,
                    'ultimo_movimiento' => $ultimo,
                    'dias_sin_movimiento' => $diasSinMovimiento,
                ];
            })
            ->filter(fn (array $fila): bool => $fila['stock_actual'] > 0 && ($fila['dias_sin_movimiento'] === null || $fila['dias_sin_movimiento'] >= $dias))
            ->sortByDesc('stock_actual')
            ->take($limite)
            ->values();
    }

    /**
     * Suma salidas por producto para un periodo reciente.
     *
     * @return Collection<string, array{cantidad: float}>
     */
    private function salidasPorProducto(int $dias): Collection
    {
        return MovimientoInventario::query()
            ->select('producto_id')
            ->selectRaw('sum(cantidad) as cantidad')
            ->where('tipo', TipoMovimientoInventario::Salida->value)
            ->where('created_at', '>=', now()->subDays(max(1, $dias)))
            ->whereNotNull('producto_id')
            ->groupBy('producto_id')
            ->get()
            ->mapWithKeys(fn (MovimientoInventario $movimiento): array => [
                (string) $movimiento->producto_id => [
                    'cantidad' => round((float) $movimiento->cantidad, 3),
                ],
            ]);
    }

}
