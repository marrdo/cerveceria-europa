<x-app-layout>
    @php
        $formatearCantidad = static function (float|string|null $cantidad): string {
            $valor = round((float) ($cantidad ?? 0), 2);

            if (abs($valor - round($valor)) < 0.001) {
                return number_format($valor, 0, ',', '.');
            }

            return rtrim(rtrim(number_format($valor, 2, ',', '.'), '0'), ',');
        };

        $maxMovimiento = max(1, (float) $graficaEntradasSalidas->max('total'));
        $puntosEntradas = $graficaEntradasSalidas->values()->map(function (array $dia, int $indice) use ($maxMovimiento, $graficaEntradasSalidas): string {
            $x = 30 + ($indice * (540 / max(1, $graficaEntradasSalidas->count() - 1)));
            $y = 150 - (((float) $dia['entradas'] / $maxMovimiento) * 120);

            return round($x, 2).','.round($y, 2);
        })->implode(' ');
        $puntosSalidas = $graficaEntradasSalidas->values()->map(function (array $dia, int $indice) use ($maxMovimiento, $graficaEntradasSalidas): string {
            $x = 30 + ($indice * (540 / max(1, $graficaEntradasSalidas->count() - 1)));
            $y = 150 - (((float) $dia['salidas'] / $maxMovimiento) * 120);

            return round($x, 2).','.round($y, 2);
        })->implode(' ');
    @endphp

    <x-slot name="header">
        <x-admin.page-header title="Inventario" description="Vista operativa de stock, alertas y movimientos recientes">
            <x-slot name="actions">
                <form method="GET" action="{{ route('admin.inventario.index') }}" class="flex items-center gap-2">
                    <x-input-label for="periodo" value="Periodo" class="sr-only" />
                    <select id="periodo" name="periodo" class="admin-input h-10 min-w-32" onchange="this.form.submit()">
                        @foreach ($periodosDisponibles as $periodoDisponible)
                            <option value="{{ $periodoDisponible }}" @selected($periodo === $periodoDisponible)>{{ $periodoDisponible }} dias</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('admin.inventario.productos.create') }}" class="admin-btn-primary">Nuevo producto</a>
                <a href="{{ route('admin.inventario.movimientos.index') }}" class="admin-btn-outline">Ver movimientos</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.inventario.partials.nav')

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores principales de inventario">
        <x-admin.kpi-card title="Productos activos" :value="$kpis['productos_activos']" description="Catalogo inventariable" />
        <x-admin.kpi-card title="Con stock real" :value="$kpis['productos_con_existencias']" description="Productos con unidades" />
        <x-admin.kpi-card title="Sin stock" :value="$kpis['productos_sin_stock']" description="Necesitan revision" variant="danger" />
        <x-admin.kpi-card title="Stock bajo" :value="$kpis['productos_bajo_stock']" description="Por debajo del minimo" variant="warning" />
        <x-admin.kpi-card title="Movimientos hoy" :value="$kpis['movimientos_hoy']" description="Actividad registrada" />
        <x-admin.kpi-card :title="'Entradas '.$periodo.' dias'" :value="$formatearCantidad($kpis['entradas_periodo'])" description="Unidades recibidas" variant="success" />
        <x-admin.kpi-card :title="'Salidas '.$periodo.' dias'" :value="$formatearCantidad($kpis['salidas_periodo'])" description="Unidades descontadas" variant="danger" />
        <x-admin.kpi-card title="Valor stock" :value="number_format($kpis['valor_stock'], 2, ',', '.').' EUR'" description="Estimado por precio coste" />
        <x-admin.kpi-card title="Carta sin stock" :value="$kpis['carta_sin_inventario']" description="Publicados sin control" variant="warning" />
        <x-admin.kpi-card title="Ventas sin salida" :value="$kpis['ventas_sin_descuento_stock']" description="Servidas sin descuento" variant="danger" />
        <x-admin.kpi-card :title="'Margen '.$periodo.' dias'" :value="number_format($kpis['margen_bruto_periodo'], 2, ',', '.').' EUR'" description="Ventas inventariables" variant="success" />
        <x-admin.kpi-card title="Descuadres repetidos" :value="$kpis['descuadres_repetidos']" description="Productos a revisar" variant="warning" />
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-[1.3fr_.7fr]" aria-label="Graficas de movimientos de inventario">
        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Entradas vs salidas</h2>
                <p class="mt-1 text-sm text-muted-foreground">Comparativa diaria de unidades recibidas y descontadas en los ultimos {{ $periodo }} dias.</p>
            </header>

            <div class="p-4">
                <div class="mb-4 flex flex-wrap gap-4 text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-success"></span>Entradas</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-destructive"></span>Salidas</span>
                </div>

                <figure class="mb-5 overflow-hidden rounded-lg border border-border bg-muted/20 p-3">
                    <svg viewBox="0 0 600 180" role="img" aria-label="Grafica lineal de entradas y salidas" class="h-48 w-full">
                        <line x1="30" y1="150" x2="570" y2="150" stroke="currentColor" class="text-border" stroke-width="1" />
                        <line x1="30" y1="30" x2="30" y2="150" stroke="currentColor" class="text-border" stroke-width="1" />
                        <polyline points="{{ $puntosEntradas }}" fill="none" stroke="#16a34a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        <polyline points="{{ $puntosSalidas }}" fill="none" stroke="#dc2626" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($graficaEntradasSalidas->values() as $indice => $dia)
                            @php
                                $x = 30 + ($indice * (540 / max(1, $graficaEntradasSalidas->count() - 1)));
                                $entradaY = 150 - (((float) $dia['entradas'] / $maxMovimiento) * 120);
                                $salidaY = 150 - (((float) $dia['salidas'] / $maxMovimiento) * 120);
                            @endphp
                            <circle cx="{{ $x }}" cy="{{ $entradaY }}" r="3.5" fill="#16a34a" />
                            <circle cx="{{ $x }}" cy="{{ $salidaY }}" r="3.5" fill="#dc2626" />
                        @endforeach
                    </svg>
                    <figcaption class="sr-only">Linea verde para entradas y linea roja para salidas.</figcaption>
                </figure>

                <ol class="space-y-3">
                    @foreach ($graficaEntradasSalidas as $dia)
                        <li class="grid gap-2 md:grid-cols-[3.5rem_1fr_6.5rem] md:items-center">
                            <time datetime="{{ $dia['fecha'] }}" class="text-xs font-medium text-muted-foreground">{{ $dia['etiqueta'] }}</time>
                            <div class="space-y-1.5">
                                <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-success" style="width: {{ $dia['porcentaje_entradas'] }}%"></div>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-destructive" style="width: {{ $dia['porcentaje_salidas'] }}%"></div>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground md:text-right">
                                <span class="font-semibold text-success">{{ $formatearCantidad($dia['entradas']) }}</span>
                                /
                                <span class="font-semibold text-destructive">{{ $formatearCantidad($dia['salidas']) }}</span>
                            </p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </article>

        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Movimientos por tipo</h2>
                <p class="mt-1 text-sm text-muted-foreground">Peso operativo de cada tipo de movimiento en los ultimos {{ $periodo }} dias.</p>
            </header>

            <div class="divide-y divide-border">
                @foreach ($graficaMovimientosPorTipo as $tipo)
                    @php
                        $barClass = match ($tipo['variant']) {
                            'success' => 'bg-success',
                            'danger' => 'bg-destructive',
                            'warning' => 'bg-warning',
                            'info' => 'bg-accent',
                            default => 'bg-primary',
                        };
                    @endphp
                    <div class="p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-foreground">{{ $tipo['etiqueta'] }}</p>
                                <p class="text-xs text-muted-foreground">{{ $tipo['movimientos'] }} movimientos</p>
                            </div>
                            <p class="text-sm font-bold text-foreground">{{ $formatearCantidad($tipo['cantidad']) }}</p>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full {{ $barClass }}" style="width: {{ $tipo['porcentaje'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-2" aria-label="Graficas de categorias y ubicaciones">
        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Salidas por categoria</h2>
                <p class="mt-1 text-sm text-muted-foreground">Categorias con mas consumo de stock en los ultimos {{ $periodo }} dias.</p>
            </header>

            <div class="divide-y divide-border">
                @forelse ($graficaSalidasPorCategoria as $categoria)
                    <div class="p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $categoria['categoria'] }}</p>
                                <p class="text-xs text-muted-foreground">{{ $categoria['movimientos'] }} movimientos de salida</p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-foreground">{{ $formatearCantidad($categoria['cantidad']) }}</p>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary" style="width: {{ $categoria['porcentaje'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">Todavia no hay salidas suficientes para agrupar por categoria.</p>
                @endforelse
            </div>
        </article>

        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Stock por ubicacion</h2>
                <p class="mt-1 text-sm text-muted-foreground">Distribucion actual de unidades entre almacenes y puntos de stock.</p>
            </header>

            <div class="divide-y divide-border">
                @forelse ($graficaStockPorUbicacion as $ubicacion)
                    <a href="{{ route('admin.inventario.productos.index', ['ubicacion_inventario_id' => $ubicacion['ubicacion_id']]) }}" class="block p-4 transition hover:bg-primary/5 focus:outline-none focus:ring-2 focus:ring-primary/40" title="Ver stock de {{ $ubicacion['ubicacion'] }}">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $ubicacion['ubicacion'] }}</p>
                                <p class="text-xs text-muted-foreground">{{ $ubicacion['lineas'] }} productos con stock</p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-foreground">{{ $formatearCantidad($ubicacion['cantidad']) }}</p>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-accent" style="width: {{ $ubicacion['porcentaje'] }}%"></div>
                        </div>
                    </a>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">Todavia no hay stock distribuido por ubicacion.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-2" aria-label="Control inteligente de stock">
        <article class="admin-card overflow-hidden">
            <header class="flex items-start justify-between gap-4 border-b border-border p-4">
                <div>
                    <h2 class="text-base font-semibold text-foreground">Reposicion urgente</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Productos que conviene revisar por stock, minimo o consumo reciente.</p>
                </div>
                <a href="{{ route('admin.compras.propuestas.index') }}" class="text-sm font-medium text-primary hover:underline">Ver propuestas</a>
            </header>

            <div class="divide-y divide-border">
                @forelse ($reposicionUrgente as $fila)
                    @php
                        $producto = $fila['producto'];
                        $diasRestantes = $fila['dias_restantes'];
                    @endphp
                    <div class="grid gap-3 p-4 sm:grid-cols-[1fr_auto]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $producto->nombre }}</p>
                                <x-admin.status-badge :variant="$fila['urgencia'] === 0 ? 'danger' : 'warning'">{{ $fila['motivo'] }}</x-admin.status-badge>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Stock {{ $producto->formatearCantidadConUnidad($fila['stock_actual']) }}
                                &middot; Salidas {{ $periodo }} dias {{ $producto->formatearCantidadConUnidad($fila['salidas_periodo']) }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Media diaria {{ $producto->formatearCantidadConUnidad($fila['consumo_medio_diario']) }}
                                &middot; {{ $diasRestantes === null ? 'Sin consumo reciente' : $diasRestantes.' dias estimados' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Stock" aria-label="Stock de {{ $producto->nombre }}">
                            <x-admin.icon name="stock" />
                        </a>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">No hay productos con reposicion urgente segun la regla actual.</p>
                @endforelse
            </div>
        </article>

        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Stock parado</h2>
                <p class="mt-1 text-sm text-muted-foreground">Productos con unidades disponibles y sin movimiento reciente.</p>
            </header>

            <div class="divide-y divide-border">
                @forelse ($stockSinMovimiento as $fila)
                    @php
                        $producto = $fila['producto'];
                    @endphp
                    <div class="grid gap-3 p-4 sm:grid-cols-[1fr_auto]">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-foreground">{{ $producto->nombre }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Stock {{ $producto->formatearCantidadConUnidad($fila['stock_actual']) }}
                                &middot; {{ $fila['dias_sin_movimiento'] === null ? 'Sin movimientos registrados' : $fila['dias_sin_movimiento'].' dias sin movimiento' }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $producto->categoria?->nombre ?? 'Sin categoria' }} &middot; {{ $producto->proveedor?->nombre ?? 'Sin proveedor' }}</p>
                        </div>
                        <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Stock" aria-label="Stock de {{ $producto->nombre }}">
                            <x-admin.icon name="stock" />
                        </a>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">No hay stock parado con el umbral actual.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-[.9fr_1.1fr]" aria-label="Cruce entre carta, ventas e inventario">
        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Carta sin control de stock</h2>
                <p class="mt-1 text-sm text-muted-foreground">Productos publicados que no tienen producto inventariable asociado o no controlan stock.</p>
            </header>

            <div class="divide-y divide-border">
                @forelse ($contenidosCartaSinInventario as $contenido)
                    @php
                        $motivo = $contenido->producto_id === null
                            ? 'Sin producto de inventario'
                            : 'Producto sin control de stock';
                    @endphp
                    <div class="grid gap-3 p-4 sm:grid-cols-[1fr_auto]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $contenido->titulo }}</p>
                                <x-admin.status-badge variant="warning">{{ $motivo }}</x-admin.status-badge>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ $contenido->categoriaCarta?->nombreJerarquico() ?? 'Sin categoria' }}
                                &middot; {{ $contenido->precioFormateado() ?? 'Sin precio' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.web-publica.contenidos.edit', $contenido) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Editar en carta" aria-label="Editar {{ $contenido->titulo }} en carta">
                            <x-admin.icon name="edit" />
                        </a>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">La carta publicada esta vinculada a inventario controlado.</p>
                @endforelse
            </div>
        </article>

        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Ventas sin descuento de stock</h2>
                <p class="mt-1 text-sm text-muted-foreground">Lineas servidas en los ultimos {{ $periodo }} dias que no han generado salida de inventario.</p>
            </header>

            <div class="divide-y divide-border">
                @forelse ($lineasServidasSinProducto as $linea)
                    <div class="grid gap-3 p-4 sm:grid-cols-[1fr_auto]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $linea->nombre }}</p>
                                <x-admin.status-badge variant="danger">Sin producto</x-admin.status-badge>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Comanda {{ $linea->comanda?->numero ?? 'sin numero' }}
                                &middot; {{ $linea->servida_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                &middot; {{ $formatearCantidad($linea->cantidad) }} uds
                            </p>
                        </div>
                        @if ($linea->comanda)
                            <a href="{{ route('admin.ventas.comandas.show', $linea->comanda) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Ver comanda" aria-label="Ver comanda {{ $linea->comanda->numero }}">
                                <x-admin.icon name="stock" />
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">No hay lineas servidas sin producto de inventario en los ultimos {{ $periodo }} dias.</p>
                @endforelse

                @forelse ($lineasInventariablesSinMovimiento as $linea)
                    <div class="grid gap-3 bg-destructive/5 p-4 sm:grid-cols-[1fr_auto]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $linea->nombre }}</p>
                                <x-admin.status-badge variant="danger">Sin movimiento</x-admin.status-badge>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ $linea->producto?->nombre ?? 'Producto eliminado' }}
                                &middot; Comanda {{ $linea->comanda?->numero ?? 'sin numero' }}
                                &middot; {{ $linea->servida_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                            </p>
                        </div>
                        @if ($linea->producto)
                            <a href="{{ route('admin.inventario.productos.stock', $linea->producto->sku ?: $linea->producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Ver stock" aria-label="Ver stock de {{ $linea->producto->nombre }}">
                                <x-admin.icon name="stock" />
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">No hay lineas inventariables servidas sin movimiento de salida.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-[1.1fr_.9fr]" aria-label="Comparativa de ventas, stock y margen">
        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Ventas vs salidas de stock</h2>
                <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-muted-foreground">Comparativa de lineas servidas frente a movimientos de salida vinculados en los ultimos {{ $periodo }} dias.</p>
                    <a href="{{ route('admin.inventario.descuadres.exportar', ['periodo' => $periodo]) }}" class="text-sm font-medium text-primary hover:underline">Exportar CSV</a>
                </div>
            </header>

            <div class="divide-y divide-border">
                @forelse ($comparativaVentasStock as $fila)
                    @php
                        $producto = $fila['producto'];
                        $hayDescuadre = abs((float) $fila['diferencia']) > 0.001;
                    @endphp
                    <div class="grid gap-3 p-4 lg:grid-cols-[1fr_8rem_8rem_7rem_auto] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $producto->nombre }}</p>
                                <x-admin.status-badge :variant="$hayDescuadre ? 'danger' : 'success'">{{ $hayDescuadre ? 'Descuadre' : 'Cuadra' }}</x-admin.status-badge>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $producto->sku ?? 'Sin SKU' }} &middot; {{ $producto->categoria?->nombre ?? 'Sin categoria' }}</p>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            <span class="block text-xs">Vendido</span>
                            <span class="font-semibold text-foreground">{{ $producto->formatearCantidadConUnidad($fila['cantidad_vendida']) }}</span>
                        </p>
                        <p class="text-sm text-muted-foreground">
                            <span class="block text-xs">Descontado</span>
                            <span class="font-semibold text-foreground">{{ $producto->formatearCantidadConUnidad($fila['cantidad_descontada']) }}</span>
                        </p>
                        <p class="text-sm text-muted-foreground">
                            <span class="block text-xs">Diferencia</span>
                            <span class="font-semibold {{ $hayDescuadre ? 'text-destructive' : 'text-success' }}">{{ $producto->formatearCantidadConUnidad($fila['diferencia']) }}</span>
                        </p>
                        <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Ver stock" aria-label="Ver stock de {{ $producto->nombre }}">
                            <x-admin.icon name="stock" />
                        </a>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">Todavia no hay ventas servidas con productos inventariables en los ultimos {{ $periodo }} dias.</p>
                @endforelse
            </div>
        </article>

        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Coste y margen estimado</h2>
                <p class="mt-1 text-sm text-muted-foreground">Calculo orientativo con lineas servidas y precio de coste del producto.</p>
            </header>

            <dl class="grid gap-3 p-4 sm:grid-cols-2">
                <div class="rounded-lg border border-border bg-muted/20 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Ingresos inventariables</dt>
                    <dd class="mt-1 text-xl font-bold text-foreground">{{ number_format($resumenEconomicoVentasInventario['ingresos'], 2, ',', '.') }} EUR</dd>
                </div>
                <div class="rounded-lg border border-border bg-muted/20 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Coste estimado</dt>
                    <dd class="mt-1 text-xl font-bold text-foreground">{{ number_format($resumenEconomicoVentasInventario['coste_estimado'], 2, ',', '.') }} EUR</dd>
                </div>
                <div class="rounded-lg border border-success/20 bg-success/10 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Margen bruto</dt>
                    <dd class="mt-1 text-xl font-bold text-foreground">{{ number_format($resumenEconomicoVentasInventario['margen_bruto'], 2, ',', '.') }} EUR</dd>
                </div>
                <div class="rounded-lg border border-border bg-muted/20 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Margen orientativo</dt>
                    <dd class="mt-1 text-xl font-bold text-foreground">
                        {{ $resumenEconomicoVentasInventario['margen_porcentaje'] === null ? '-' : number_format($resumenEconomicoVentasInventario['margen_porcentaje'], 2, ',', '.').' %' }}
                    </dd>
                </div>
            </dl>

            <div class="border-t border-border p-4 text-sm text-muted-foreground">
                <p>
                    Unidades vendidas {{ $formatearCantidad($resumenEconomicoVentasInventario['unidades_vendidas']) }}
                    &middot; descontadas {{ $formatearCantidad($resumenEconomicoVentasInventario['unidades_descontadas']) }}
                    &middot; productos con descuadre {{ $resumenEconomicoVentasInventario['productos_con_descuadre'] }}
                </p>
            </div>
        </article>
    </section>

    <section class="mt-6" aria-label="Alertas de descuadres repetidos">
        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Alertas por descuadres repetidos</h2>
                <p class="mt-1 text-sm text-muted-foreground">Productos con dos o mas incidencias entre cantidad servida y salida de stock en los ultimos {{ $periodo }} dias.</p>
            </header>

            <div class="divide-y divide-border">
                @if ($descuadresRepetidos->isEmpty())
                    <p class="p-4 text-sm text-muted-foreground">No hay descuadres repetidos en este periodo.</p>
                @else
                    @foreach ($descuadresRepetidos as $descuadre)
                        @php
                            $producto = $descuadre['producto'];
                        @endphp
                        <div class="grid gap-3 p-4 md:grid-cols-[1fr_8rem_9rem_10rem_auto] md:items-center">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-foreground">{{ $producto->nombre }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">{{ $producto->sku ?? 'Sin SKU' }} &middot; {{ $producto->categoria?->nombre ?? 'Sin categoria' }}</p>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                <span class="block text-xs">Incidencias</span>
                                <span class="font-semibold text-foreground">{{ $descuadre['incidencias'] }}</span>
                            </p>
                            <p class="text-sm text-muted-foreground">
                                <span class="block text-xs">Diferencia</span>
                                <span class="font-semibold text-destructive">{{ $producto->formatearCantidadConUnidad($descuadre['diferencia_total']) }}</span>
                            </p>
                            <p class="text-sm text-muted-foreground">
                                <span class="block text-xs">Ultima</span>
                                <span class="font-semibold text-foreground">{{ $descuadre['ultima_incidencia'] ?? '-' }}</span>
                            </p>
                            <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Ver stock" aria-label="Ver stock de {{ $producto->nombre }}">
                                <x-admin.icon name="stock" />
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-[1.1fr_.9fr]" aria-label="Acciones y alertas de inventario">
        <article class="admin-card p-4 lg:p-6">
            <header class="mb-4">
                <h2 class="text-base font-semibold text-foreground">Acciones rapidas</h2>
                <p class="mt-1 text-sm text-muted-foreground">Atajos para trabajar sin recorrer todo el modulo.</p>
            </header>

            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.inventario.productos.index') }}" class="rounded-lg border border-border bg-muted/30 p-4 hover:border-primary hover:bg-primary/5">
                    <span class="block text-sm font-semibold text-foreground">Registrar movimiento</span>
                    <span class="mt-1 block text-xs text-muted-foreground">Busca un producto y entra en su ficha de stock.</span>
                </a>
                <a href="{{ route('admin.inventario.alertas.index') }}" class="rounded-lg border border-border bg-muted/30 p-4 hover:border-primary hover:bg-primary/5">
                    <span class="block text-sm font-semibold text-foreground">Revisar alertas</span>
                    <span class="mt-1 block text-xs text-muted-foreground">Sin stock, bajo minimo y caducidades.</span>
                </a>
                <a href="{{ route('admin.compras.propuestas.index') }}" class="rounded-lg border border-border bg-muted/30 p-4 hover:border-primary hover:bg-primary/5">
                    <span class="block text-sm font-semibold text-foreground">Propuesta de compra</span>
                    <span class="mt-1 block text-xs text-muted-foreground">Generar reposicion desde stock bajo.</span>
                </a>
                <a href="{{ route('admin.inventario.ubicaciones.index') }}" class="rounded-lg border border-border bg-muted/30 p-4 hover:border-primary hover:bg-primary/5">
                    <span class="block text-sm font-semibold text-foreground">Ubicaciones</span>
                    <span class="mt-1 block text-xs text-muted-foreground">Almacen, camara, barra y puntos de stock.</span>
                </a>
            </div>
        </article>

        <article class="admin-card p-4 lg:p-6">
            <header class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-foreground">Resumen critico</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Lo primero que deberia mirar el encargado.</p>
                </div>
                <a href="{{ route('admin.inventario.alertas.index') }}" class="text-sm font-medium text-primary hover:underline">Ver todo</a>
            </header>

            <dl class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-destructive/20 bg-destructive/10 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Productos sin stock</dt>
                    <dd class="mt-1 text-2xl font-bold text-foreground">{{ $productosSinStock->count() }}</dd>
                </div>
                <div class="rounded-lg border border-warning/20 bg-warning/10 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Productos bajo minimo</dt>
                    <dd class="mt-1 text-2xl font-bold text-foreground">{{ $productosBajoStock->count() }}</dd>
                </div>
                <div class="rounded-lg border border-destructive/20 bg-destructive/10 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Lotes caducados</dt>
                    <dd class="mt-1 text-2xl font-bold text-foreground">{{ $lotesCaducados->count() }}</dd>
                </div>
                <div class="rounded-lg border border-warning/20 bg-warning/10 p-3">
                    <dt class="text-xs font-medium text-muted-foreground">Caducan pronto</dt>
                    <dd class="mt-1 text-2xl font-bold text-foreground">{{ $lotesProximosCaducar->count() }}</dd>
                </div>
            </dl>
        </article>
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-2" aria-label="Productos que requieren accion">
        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Productos sin stock</h2>
                <p class="mt-1 text-sm text-muted-foreground">Primeros productos que no tienen unidades disponibles.</p>
            </header>
            <div class="divide-y divide-border">
                @forelse ($productosSinStock as $producto)
                    @include('modulos.inventario.partials.dashboard-producto-alerta', ['producto' => $producto, 'variant' => 'danger', 'label' => 'Sin stock'])
                @empty
                    <p class="p-4 text-sm text-muted-foreground">No hay productos sin stock.</p>
                @endforelse
            </div>
        </article>

        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Stock bajo</h2>
                <p class="mt-1 text-sm text-muted-foreground">Productos por debajo de su cantidad de alerta.</p>
            </header>
            <div class="divide-y divide-border">
                @forelse ($productosBajoStock as $producto)
                    @include('modulos.inventario.partials.dashboard-producto-alerta', ['producto' => $producto, 'variant' => 'warning', 'label' => 'Stock bajo'])
                @empty
                    <p class="p-4 text-sm text-muted-foreground">No hay productos con stock bajo.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-[.9fr_1.1fr]" aria-label="Rotacion y movimientos recientes">
        <article class="admin-card overflow-hidden">
            <header class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Top salidas {{ $periodo }} dias</h2>
                <p class="mt-1 text-sm text-muted-foreground">Productos con mayor descuento de stock reciente.</p>
            </header>
            <div class="divide-y divide-border">
                @forelse ($topSalidas as $movimiento)
                    <div class="flex items-center justify-between gap-4 p-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-foreground">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $movimiento->producto?->sku ?? 'Sin SKU' }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-foreground">
                            {{ $movimiento->producto?->formatearCantidadConUnidad($movimiento->cantidad_total) ?? $formatearCantidad($movimiento->cantidad_total) }}
                        </p>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">Todavia no hay salidas registradas en los ultimos {{ $periodo }} dias.</p>
                @endforelse
            </div>
        </article>

        <article class="admin-card overflow-hidden">
            <header class="flex items-start justify-between gap-4 border-b border-border p-4">
                <div>
                    <h2 class="text-base font-semibold text-foreground">Ultimos movimientos</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Actividad reciente de entradas, salidas, ajustes y transferencias.</p>
                </div>
                <a href="{{ route('admin.inventario.movimientos.index') }}" class="text-sm font-medium text-primary hover:underline">Ver informe</a>
            </header>
            <div class="divide-y divide-border">
                @forelse ($ultimosMovimientos as $movimiento)
                    @php
                        $variant = match ($movimiento->tipo->value) {
                            'entrada' => 'success',
                            'salida' => 'danger',
                            'ajuste' => 'warning',
                            'transferencia' => 'info',
                            default => 'default',
                        };

                        $ubicacionTexto = $movimiento->tipo->value === 'transferencia'
                            ? (($movimiento->ubicacionOrigen?->nombre ?? 'Sin origen').' -> '.($movimiento->ubicacionDestino?->nombre ?? 'Sin destino'))
                            : ($movimiento->ubicacion?->nombre ?? 'Sin ubicacion');
                    @endphp
                    <div class="grid gap-3 p-4 sm:grid-cols-[1fr_auto]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-admin.status-badge :variant="$variant">{{ $movimiento->tipo->etiqueta() }}</x-admin.status-badge>
                                <span class="text-xs text-muted-foreground">{{ $movimiento->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mt-2 truncate text-sm font-medium text-foreground">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $ubicacionTexto }} &middot; {{ $movimiento->motivo ?: 'Sin motivo' }}</p>
                        </div>
                        <p class="text-sm font-bold text-foreground">
                            {{ $movimiento->producto?->formatearCantidadConUnidad($movimiento->cantidad) ?? \App\Support\Formato\FormateadorCantidad::formatear($movimiento->cantidad) }}
                        </p>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">Todavia no hay movimientos registrados.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-app-layout>
