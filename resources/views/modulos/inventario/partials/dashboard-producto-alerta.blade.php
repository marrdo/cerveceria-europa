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
            Stock {{ $producto->formatearCantidad($producto->cantidadStock()) }} {{ $producto->codigoUnidad() }}
            · Alerta {{ $producto->formatearCantidad($producto->cantidad_alerta_stock) }} {{ $producto->codigoUnidad() }}
        </p>
    </div>
    <a href="{{ route('admin.inventario.productos.stock', $producto) }}" class="self-center text-sm font-medium text-primary hover:underline">Ver stock</a>
</div>
