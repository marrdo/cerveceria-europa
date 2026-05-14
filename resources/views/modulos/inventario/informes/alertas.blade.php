<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Alertas de inventario" description="Productos que necesitan revision operativa">
            <x-slot name="actions">
                <a href="{{ route('admin.inventario.alertas.exportar') }}" class="admin-btn-outline">Exportar CSV</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.inventario.partials.nav')

    <div class="grid gap-4 xl:grid-cols-2">
        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Productos sin stock</h2>
                <p class="mt-1 text-sm text-muted-foreground">{{ $productosSinStock->count() }} productos sin unidades disponibles.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Proveedor</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Alerta</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productosSinStock as $producto)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $producto->nombre }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $producto->sku ?? 'Sin SKU' }} - {{ $producto->categoria?->nombre ?? 'Sin categoria' }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $producto->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                                <td class="px-4 py-3">
                                    <x-admin.status-badge variant="danger">Sin stock</x-admin.status-badge>
                                    <div class="mt-1 text-xs text-muted-foreground">Minimo {{ $producto->formatearCantidadConUnidad($producto->cantidad_alerta_stock) }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Stock" aria-label="Stock de {{ $producto->nombre }}">
                                        <x-admin.icon name="stock" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay productos sin stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Stock bajo</h2>
                <p class="mt-1 text-sm text-muted-foreground">{{ $productosBajoStock->count() }} productos por debajo de su cantidad de alerta.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Stock</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Proveedor</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productosBajoStock as $producto)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $producto->nombre }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $producto->sku ?? 'Sin SKU' }} - {{ $producto->categoria?->nombre ?? 'Sin categoria' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-admin.status-badge variant="warning">Stock bajo</x-admin.status-badge>
                                    <div class="mt-1 text-xs text-muted-foreground">{{ $producto->formatearCantidadConUnidad($producto->cantidadStock()) }} / {{ $producto->formatearCantidadConUnidad($producto->cantidad_alerta_stock) }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $producto->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Stock" aria-label="Stock de {{ $producto->nombre }}">
                                        <x-admin.icon name="stock" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay productos con stock bajo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Lotes caducados</h2>
                <p class="mt-1 text-sm text-muted-foreground">{{ $lotesCaducados->count() }} lotes con stock disponible y fecha vencida.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Lote</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Disponible</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Caduca</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lotesCaducados as $lote)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $lote->producto?->nombre ?? 'Producto eliminado' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $lote->ubicacion?->nombre ?? 'Sin ubicacion' }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $lote->codigo_lote ?: 'Sin lote' }}</td>
                                <td class="px-4 py-3 text-foreground">{{ $lote->producto?->formatearCantidadConUnidad($lote->cantidad_disponible) ?? $lote->cantidad_disponible }}</td>
                                <td class="px-4 py-3"><x-admin.status-badge variant="danger">{{ $lote->caduca_el?->format('d/m/Y') }}</x-admin.status-badge></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay lotes caducados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Lotes proximos a caducar</h2>
                <p class="mt-1 text-sm text-muted-foreground">{{ $lotesProximosCaducar->count() }} lotes caducan en los proximos 30 dias.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Lote</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Disponible</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Caduca</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lotesProximosCaducar as $lote)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $lote->producto?->nombre ?? 'Producto eliminado' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $lote->ubicacion?->nombre ?? 'Sin ubicacion' }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $lote->codigo_lote ?: 'Sin lote' }}</td>
                                <td class="px-4 py-3 text-foreground">{{ $lote->producto?->formatearCantidadConUnidad($lote->cantidad_disponible) ?? $lote->cantidad_disponible }}</td>
                                <td class="px-4 py-3"><x-admin.status-badge variant="warning">{{ $lote->caduca_el?->format('d/m/Y') }}</x-admin.status-badge></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay lotes proximos a caducar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
