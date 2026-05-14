<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Productos" :description="$productos->total().' productos en el catalogo'">
            <x-slot name="actions">
                <a href="{{ route('admin.inventario.productos.exportar') }}" class="admin-btn-outline">Exportar CSV</a>
                <a href="{{ route('admin.inventario.productos.create') }}" class="admin-btn-primary">Nuevo producto</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

            @include('modulos.inventario.partials.nav')

            @if (session('status'))
                <div class="mb-4 rounded-md border border-success/30 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.inventario.productos.index') }}" class="admin-card mb-4 grid gap-3 p-4 lg:grid-cols-7">
                <div class="lg:col-span-2">
                    <x-input-label for="busqueda" value="Busqueda" />
                    <x-text-input id="busqueda" name="busqueda" type="search" class="mt-1 block h-10 w-full" :value="$filtros['busqueda']" placeholder="Escanea codigo, SKU o busca por nombre" maxlength="191" autocomplete="off" enterkeyhint="search" autofocus />
                    <p class="mt-1 text-xs text-muted-foreground">El lector de codigo funciona como teclado: escanea y pulsa filtrar.</p>
                </div>

                <div>
                    <x-input-label for="categoria_producto_id" value="Categoria" />
                    <select id="categoria_producto_id" name="categoria_producto_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Todas</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected($filtros['categoria_producto_id'] === $categoria->id)>{{ $categoria->nombre }}</option>
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
                    <x-input-label for="ubicacion_inventario_id" value="Ubicacion" />
                    <select id="ubicacion_inventario_id" name="ubicacion_inventario_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Todas</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected($filtros['ubicacion_inventario_id'] === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="estado_stock" value="Estado stock" />
                    <select id="estado_stock" name="estado_stock" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Todos</option>
                        @foreach ($estadosStock as $estadoStock)
                            <option value="{{ $estadoStock->value }}" @selected($filtros['estado_stock'] === $estadoStock->value)>{{ $estadoStock->etiqueta() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="activo" value="Estado" />
                    <select id="activo" name="activo" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Todos</option>
                        <option value="1" @selected($filtros['activo'] === '1')>Activos</option>
                        <option value="0" @selected($filtros['activo'] === '0')>Inactivos</option>
                    </select>
                </div>

                <div class="flex items-end gap-2 lg:col-span-7">
                    <button type="submit" class="admin-btn-primary">Filtrar</button>
                    <a href="{{ route('admin.inventario.productos.index') }}" class="admin-btn-outline">Limpiar</a>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg border border-border bg-card">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                            <th class="hidden px-4 py-3 text-left font-medium text-foreground md:table-cell">Categoria</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Stock</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productos as $producto)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-foreground">{{ $producto->nombre }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $producto->sku ?? 'Sin SKU' }}</div>
                                </td>
                                <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ $producto->categoria?->nombre }}</td>
                                <td class="px-4 py-3 text-foreground">{{ $producto->formatearCantidadConUnidad($producto->cantidadStock()) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $estado = $producto->estadoStock();
                                        $variant = match ($estado->value) {
                                            'correcto' => 'success',
                                            'bajo' => 'warning',
                                            'sin_stock' => 'danger',
                                            default => 'default',
                                        };
                                    @endphp
                                    <x-admin.status-badge :variant="$variant">{{ $estado->etiqueta() }}</x-admin.status-badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.inventario.productos.stock', $producto->sku ?: $producto->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Stock" aria-label="Stock de {{ $producto->nombre }}">
                                            <x-admin.icon name="stock" />
                                        </a>
                                        <a href="{{ route('admin.inventario.productos.edit', $producto) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Editar" aria-label="Editar {{ $producto->nombre }}">
                                            <x-admin.icon name="edit" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay productos todavia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $productos->links() }}</div>
</x-app-layout>
