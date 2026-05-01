<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Pedidos de compra" :description="$pedidos->total().' pedidos registrados'">
            <x-slot name="actions">
                <a href="{{ route('admin.compras.pedidos.create') }}" class="admin-btn-primary">Nuevo pedido</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.compras.pedidos.index') }}" class="admin-card mb-4 grid gap-3 p-4 lg:grid-cols-4">
        <div>
            <x-input-label for="busqueda" value="Numero" />
            <x-text-input id="busqueda" name="busqueda" class="mt-1 block h-10 w-full" :value="$filtros['busqueda']" maxlength="50" />
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
            <x-input-label for="estado" value="Estado" />
            <select id="estado" name="estado" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todos</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado->value }}" @selected($filtros['estado'] === $estado->value)>{{ $estado->etiqueta() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="admin-btn-primary">Filtrar</button>
            <a href="{{ route('admin.compras.pedidos.index') }}" class="admin-btn-outline">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Numero</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Proveedor</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Fecha</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $pedido->numero }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $pedido->proveedor?->nombre }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$pedido->estado->variante()">{{ $pedido->estado->etiqueta() }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $pedido->fecha_pedido?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-foreground">{{ number_format((float) $pedido->total, 2, ',', '.') }} EUR</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.compras.pedidos.show', $pedido) }}" class="text-primary hover:underline">Ver</a>
                            @if ($pedido->puedeEditar())
                                <a href="{{ route('admin.compras.pedidos.edit', $pedido) }}" class="ms-3 text-primary hover:underline">Editar</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay pedidos de compra todavia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pedidos->links() }}</div>
</x-app-layout>
