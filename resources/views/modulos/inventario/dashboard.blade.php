<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Inventario" description="Vista operativa de stock, alertas y movimientos recientes">
            <x-slot name="actions">
                <a href="{{ route('admin.inventario.productos.create') }}" class="admin-btn-primary">Nuevo producto</a>
                <a href="{{ route('admin.inventario.movimientos.index') }}" class="admin-btn-outline">Ver movimientos</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.inventario.partials.nav')

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores principales de inventario">
        <x-admin.kpi-card title="Productos activos" :value="$kpis['productos_activos']" description="Catalogo disponible" />
        <x-admin.kpi-card title="Controlan stock" :value="$kpis['productos_control_stock']" description="Productos con existencias reales" />
        <x-admin.kpi-card title="Sin stock" :value="$kpis['productos_sin_stock']" description="Necesitan revision" variant="danger" />
        <x-admin.kpi-card title="Stock bajo" :value="$kpis['productos_bajo_stock']" description="Por debajo del minimo" variant="warning" />
        <x-admin.kpi-card title="Movimientos hoy" :value="$kpis['movimientos_hoy']" description="Actividad registrada" />
        <x-admin.kpi-card title="Entradas 7 dias" :value="number_format($kpis['entradas_7_dias'], 3, ',', '.')" description="Unidades recibidas" variant="success" />
        <x-admin.kpi-card title="Salidas 7 dias" :value="number_format($kpis['salidas_7_dias'], 3, ',', '.')" description="Unidades descontadas" variant="danger" />
        <x-admin.kpi-card title="Valor stock" :value="number_format($kpis['valor_stock'], 2, ',', '.').' EUR'" description="Estimado por precio coste" />
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
                <h2 class="text-base font-semibold text-foreground">Top salidas 30 dias</h2>
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
                            {{ $movimiento->producto?->formatearCantidad($movimiento->cantidad_total) ?? number_format((float) $movimiento->cantidad_total, 3, ',', '.') }}
                            {{ $movimiento->producto?->codigoUnidad() }}
                        </p>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">Todavia no hay salidas registradas en los ultimos 30 dias.</p>
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
                            <p class="mt-1 text-xs text-muted-foreground">{{ $ubicacionTexto }} · {{ $movimiento->motivo ?: 'Sin motivo' }}</p>
                        </div>
                        <p class="text-sm font-bold text-foreground">
                            {{ $movimiento->producto?->formatearCantidad($movimiento->cantidad) ?? $movimiento->cantidad }}
                            {{ $movimiento->producto?->codigoUnidad() }}
                        </p>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted-foreground">Todavia no hay movimientos registrados.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-app-layout>
