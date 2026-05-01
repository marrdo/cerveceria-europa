<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Movimientos de inventario" :description="$movimientos->total().' movimientos registrados'">
            <x-slot name="actions">
                <a href="{{ route('admin.inventario.movimientos.exportar', request()->query()) }}" class="admin-btn-outline">Exportar CSV</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.inventario.partials.nav')

    <form method="GET" action="{{ route('admin.inventario.movimientos.index') }}" class="admin-card mb-4 grid gap-3 p-4 xl:grid-cols-6">
        <div>
            <x-input-label for="fecha_desde" value="Desde" />
            <x-text-input id="fecha_desde" name="fecha_desde" type="date" class="mt-1 block h-10 w-full" :value="$filtros['fecha_desde']" />
        </div>

        <div>
            <x-input-label for="fecha_hasta" value="Hasta" />
            <x-text-input id="fecha_hasta" name="fecha_hasta" type="date" class="mt-1 block h-10 w-full" :value="$filtros['fecha_hasta']" />
        </div>

        <div>
            <x-input-label for="producto_id" value="Producto" />
            <select id="producto_id" name="producto_id" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todos</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id }}" @selected($filtros['producto_id'] === $producto->id)>{{ $producto->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="proveedor_id" value="Proveedor" />
            <select id="proveedor_id" name="proveedor_id" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todos</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @selected($filtros['proveedor_id'] === $proveedor->id)>{{ $proveedor->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="ubicacion_id" value="Ubicacion" />
            <select id="ubicacion_id" name="ubicacion_id" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todas</option>
                @foreach ($ubicaciones as $ubicacion)
                    <option value="{{ $ubicacion->id }}" @selected($filtros['ubicacion_id'] === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="tipo" value="Tipo" />
            <select id="tipo" name="tipo" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todos</option>
                @foreach ($tiposMovimiento as $tipoMovimiento)
                    <option value="{{ $tipoMovimiento->value }}" @selected($filtros['tipo'] === $tipoMovimiento->value)>{{ $tipoMovimiento->etiqueta() }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2 xl:col-span-6">
            <button type="submit" class="admin-btn-primary">Filtrar</button>
            <a href="{{ route('admin.inventario.movimientos.index') }}" class="admin-btn-outline">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Fecha</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Tipo</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Cantidad</th>
                    <th class="hidden px-4 py-3 text-left font-medium text-foreground lg:table-cell">Ubicacion</th>
                    <th class="hidden px-4 py-3 text-left font-medium text-foreground xl:table-cell">Proveedor</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Motivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimientos as $movimiento)
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
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3 text-muted-foreground">{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</div>
                            <div class="text-xs text-muted-foreground">{{ $movimiento->producto?->sku ?? 'Sin SKU' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-admin.status-badge :variant="$variant">{{ $movimiento->tipo->etiqueta() }}</x-admin.status-badge>
                        </td>
                        <td class="px-4 py-3 text-foreground">
                            {{ $movimiento->producto?->formatearCantidad($movimiento->cantidad) ?? $movimiento->cantidad }}
                            {{ $movimiento->producto?->codigoUnidad() }}
                        </td>
                        <td class="hidden px-4 py-3 text-muted-foreground lg:table-cell">{{ $ubicacionTexto }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground xl:table-cell">{{ $movimiento->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                        <td class="px-4 py-3">
                            <div class="text-foreground">{{ $movimiento->motivo ?: 'Sin motivo' }}</div>
                            @if ($movimiento->referencia)
                                <div class="text-xs text-muted-foreground">{{ $movimiento->referencia }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay movimientos con estos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $movimientos->links() }}</div>
</x-app-layout>
