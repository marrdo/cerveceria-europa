<div class="grid gap-3 p-4 sm:grid-cols-[1fr_auto]">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <p class="truncate text-sm font-medium text-foreground">{{ $producto->nombre }}</p>
            <x-admin.status-badge :variant="$variant">{{ $label }}</x-admin.status-badge>
        </div>
        <p class="mt-1 text-xs text-muted-foreground">
            {{ $producto->sku ?? 'Sin SKU' }} · {{ $producto->categoria?->nombre ?? 'Sin categoria' }} · {{ $producto->proveedor?->nombre ?? 'Sin proveedor' }}
        </p>
        <p class="mt-1 text-xs text-muted-foreground">
            Stock {{ $producto->formatearCantidadConUnidad($producto->cantidadStock()) }}
            · Alerta {{ $producto->formatearCantidadConUnidad($producto->cantidad_alerta_stock) }}
        </p>
    </div>
    <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center self-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Stock" aria-label="Stock de {{ $producto->nombre }}">
        <x-admin.icon name="stock" />
    </a>
</div>
