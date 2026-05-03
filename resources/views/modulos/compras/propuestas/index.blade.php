<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Propuestas de compra" description="Reposicion sugerida desde alertas de stock">
            <x-slot name="actions">
                <a href="{{ route('admin.compras.pedidos.create') }}" class="admin-btn-outline">Nuevo pedido manual</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <section class="admin-card mb-6 p-4 lg:p-6">
        <h2 class="text-base font-semibold text-foreground">Criterio de propuesta</h2>
        <p class="mt-2 text-sm text-muted-foreground">Se proponen productos activos con control de stock que estan sin stock o por debajo de su alerta. La cantidad sugerida intenta reponer hasta el doble de la alerta configurada.</p>
    </section>

    <div class="space-y-6">
        @forelse ($grupos as $grupo)
            <form method="POST" action="{{ route('admin.compras.propuestas.store') }}" class="admin-card overflow-hidden">
                @csrf
                <input type="hidden" name="proveedor_id" value="{{ $grupo['proveedor']->id }}">

                <div class="border-b border-border p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-foreground">{{ $grupo['proveedor']->nombre }}</h2>
                            <p class="mt-1 text-sm text-muted-foreground">Productos que necesitan reposicion para este proveedor.</p>
                        </div>
                        <x-primary-button>Generar pedido borrador</x-primary-button>
                    </div>
                    <x-input-error :messages="$errors->get('productos')" class="mt-2" />
                    <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50 align-top">
                                <th class="min-w-64 px-4 py-3 text-left font-medium text-foreground">
                                    Producto
                                    <p class="mt-1 text-xs font-normal text-muted-foreground">Articulo con stock bajo o agotado.</p>
                                </th>
                                <th class="w-36 px-4 py-3 text-left font-medium text-foreground">
                                    Stock
                                    <p class="mt-1 text-xs font-normal text-muted-foreground">Cantidad total actual.</p>
                                </th>
                                <th class="w-36 px-4 py-3 text-left font-medium text-foreground">
                                    Alerta
                                    <p class="mt-1 text-xs font-normal text-muted-foreground">Nivel minimo configurado.</p>
                                </th>
                                <th class="w-44 px-4 py-3 text-left font-medium text-foreground">
                                    Cantidad propuesta
                                    <p class="mt-1 text-xs font-normal text-muted-foreground">Puedes ajustarla antes de crear el pedido.</p>
                                </th>
                                <th class="w-36 px-4 py-3 text-right font-medium text-foreground">
                                    Coste sin IVA
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grupo['productos'] as $indice => $propuesta)
                                @php($producto = $propuesta['producto'])
                                <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="productos[{{ $indice }}][producto_id]" value="{{ $producto->id }}">
                                        <div class="font-medium text-foreground">{{ $producto->nombre }}</div>
                                        <div class="text-xs text-muted-foreground">{{ $producto->sku ?? 'Sin SKU' }}</div>
                                        <x-input-error :messages="$errors->get('productos.'.$indice.'.producto_id')" class="mt-2" />
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $producto->formatearCantidad($propuesta['stock_actual']) }} {{ $producto->codigoUnidad() }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $producto->formatearCantidad($producto->cantidad_alerta_stock) }} {{ $producto->codigoUnidad() }}</td>
                                    <td class="px-4 py-3">
                                        <x-text-input name="productos[{{ $indice }}][cantidad]" type="number" step="0.001" min="0.001" class="block h-10 w-full" :value="old('productos.'.$indice.'.cantidad', $propuesta['cantidad_sugerida'])" required />
                                        <x-input-error :messages="$errors->get('productos.'.$indice.'.cantidad')" class="mt-2" />
                                    </td>
                                    <td class="px-4 py-3 text-right text-foreground">{{ number_format((float) $producto->precio_coste, 2, ',', '.') }} EUR</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        @empty
            <section class="admin-card p-8 text-center text-sm text-muted-foreground">
                No hay productos con propuesta de compra ahora mismo.
            </section>
        @endforelse
    </div>

    @if ($productosSinProveedor->isNotEmpty())
        <section class="admin-card mt-6 overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Productos sin proveedor</h2>
                <p class="mt-1 text-sm text-muted-foreground">Estos productos tienen alerta de stock, pero no pueden generar pedido automatico hasta asignarles proveedor principal.</p>
            </div>
            <div class="divide-y divide-border">
                @foreach ($productosSinProveedor as $producto)
                    <div class="flex items-center justify-between gap-4 p-4 text-sm">
                        <div>
                            <div class="font-medium text-foreground">{{ $producto->nombre }}</div>
                            <div class="text-xs text-muted-foreground">Stock {{ $producto->formatearCantidad($producto->cantidadStock()) }} {{ $producto->codigoUnidad() }}</div>
                        </div>
                        <a href="{{ route('admin.inventario.productos.edit', $producto) }}" class="text-primary hover:underline">Asignar proveedor</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
