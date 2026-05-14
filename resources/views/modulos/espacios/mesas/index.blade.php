<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Mesas" :description="$mesas->total().' mesas configuradas'">
            <x-slot name="actions">
                <a href="{{ route('admin.espacios.mesas.create') }}" class="admin-btn-primary">Nueva mesa</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.espacios.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/30 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.espacios.mesas.index') }}" class="admin-card mb-4 grid gap-3 p-4 md:grid-cols-3">
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
            <a href="{{ route('admin.espacios.mesas.index') }}" class="admin-btn-outline">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Mesa</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Zona</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Capacidad</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mesas as $mesa)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $mesa->nombre }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $mesa->zona?->nombre ?? '-' }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $mesa->capacidad ?? '-' }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$mesa->activa ? 'success' : 'default'">{{ $mesa->activa ? 'Activa' : 'Inactiva' }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.espacios.mesas.edit', $mesa) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-primary transition hover:bg-primary/10" title="Editar" aria-label="Editar {{ $mesa->nombre }}">
                                    <x-admin.icon name="edit" />
                                </a>
                                <form method="POST" action="{{ route('admin.espacios.mesas.destroy', $mesa) }}" class="inline" onsubmit="return confirm('Seguro que deseas eliminar este registro?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-destructive transition hover:bg-destructive/10" title="Eliminar" aria-label="Eliminar {{ $mesa->nombre }}">
                                        <x-admin.icon name="trash" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay mesas configuradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $mesas->links() }}</div>
</x-app-layout>
