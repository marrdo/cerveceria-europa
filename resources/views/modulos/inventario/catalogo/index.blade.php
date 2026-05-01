<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$titulo.'s'" description="Catalogo operativo del inventario">
            <x-slot name="actions">
                <a href="{{ route($rutaBase.'.create') }}" class="admin-btn-primary">Nuevo</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

            @include('modulos.inventario.partials.nav')

            @if (session('status'))
                <div class="mb-4 rounded-md border border-success/30 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
            @endif

            <form method="GET" action="{{ route($rutaBase.'.index') }}" class="admin-card mb-4 grid gap-3 p-4 md:grid-cols-4">
                <div>
                    <x-input-label for="busqueda" value="Nombre" />
                    <x-text-input id="busqueda" name="busqueda" class="mt-1 block h-10 w-full" :value="$filtros['busqueda']" maxlength="191" />
                </div>

                @if ($permiteFiltroContacto)
                    <div>
                        <x-input-label for="contacto" value="Codigo / contacto" />
                        <x-text-input id="contacto" name="contacto" class="mt-1 block h-10 w-full" :value="$filtros['contacto']" maxlength="191" />
                    </div>
                @endif

                <div>
                    <x-input-label for="activo" value="Estado" />
                    <select id="activo" name="activo" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Todos</option>
                        <option value="1" @selected($filtros['activo'] === '1')>Activos</option>
                        <option value="0" @selected($filtros['activo'] === '0')>Inactivos</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="admin-btn-primary">Filtrar</button>
                    <a href="{{ route($rutaBase.'.index') }}" class="admin-btn-outline">Limpiar</a>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg border border-border bg-card">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Nombre</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Codigo / contacto</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $item->nombre }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $item->codigo ?? $item->email ?? $item->telefono ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <x-admin.status-badge :variant="$item->activo ? 'success' : 'default'">{{ $item->activo ? 'Activo' : 'Inactivo' }}</x-admin.status-badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route($rutaBase.'.edit', $item) }}" class="text-primary hover:underline">Editar</a>
                                    <form method="POST" action="{{ route($rutaBase.'.destroy', $item) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ms-3 text-destructive hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay registros todavia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $items->links() }}</div>
</x-app-layout>
