<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Zonas" :description="$zonas->total().' zonas configuradas'">
            <x-slot name="actions">
                <a href="{{ route('admin.espacios.zonas.create') }}" class="admin-btn-primary">Nueva zona</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.espacios.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/30 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.espacios.zonas.index') }}" class="admin-card mb-4 grid gap-3 p-4 md:grid-cols-3">
        <div>
            <x-input-label for="busqueda" value="Nombre" />
            <x-text-input id="busqueda" name="busqueda" class="mt-1 block h-10 w-full" :value="$filtros['busqueda']" maxlength="191" />
        </div>
        <div>
            <x-input-label for="activa" value="Estado" />
            <select id="activa" name="activa" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todas</option>
                <option value="1" @selected($filtros['activa'] === '1')>Activas</option>
                <option value="0" @selected($filtros['activa'] === '0')>Inactivas</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="admin-btn-primary">Filtrar</button>
            <a href="{{ route('admin.espacios.zonas.index') }}" class="admin-btn-outline">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Zona</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Recinto</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Mesas</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($zonas as $zona)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $zona->nombre }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $zona->recinto?->nombre_comercial ?? '-' }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $zona->mesas_count }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$zona->activa ? 'success' : 'default'">{{ $zona->activa ? 'Activa' : 'Inactiva' }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.espacios.zonas.edit', $zona) }}" class="text-primary hover:underline">Editar</a>
                            <form method="POST" action="{{ route('admin.espacios.zonas.destroy', $zona) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="ms-3 text-destructive hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay zonas configuradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $zonas->links() }}</div>
</x-app-layout>
