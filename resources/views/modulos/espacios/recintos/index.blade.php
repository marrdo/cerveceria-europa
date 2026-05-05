<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Recintos" :description="$recintos->total().' recintos configurados'">
            <x-slot name="actions">
                <a href="{{ route('admin.espacios.recintos.create') }}" class="admin-btn-primary">Nuevo recinto</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.espacios.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/30 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.espacios.recintos.index') }}" class="admin-card mb-4 grid gap-3 p-4 md:grid-cols-3">
        <div>
            <x-input-label for="busqueda" value="Nombre" />
            <x-text-input id="busqueda" name="busqueda" class="mt-1 block h-10 w-full" :value="$filtros['busqueda']" maxlength="191" />
        </div>
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
            <a href="{{ route('admin.espacios.recintos.index') }}" class="admin-btn-outline">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Nombre</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Contacto</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Zonas</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recintos as $recinto)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $recinto->nombre_comercial }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $recinto->telefono ?? $recinto->email ?? '-' }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $recinto->zonas_count }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$recinto->activo ? 'success' : 'default'">{{ $recinto->activo ? 'Activo' : 'Inactivo' }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.espacios.recintos.edit', $recinto) }}" class="text-primary hover:underline">Editar</a>
                            <form method="POST" action="{{ route('admin.espacios.recintos.destroy', $recinto) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="ms-3 text-destructive hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay recintos configurados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $recintos->links() }}</div>
</x-app-layout>
