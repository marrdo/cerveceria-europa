<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Comandas" :description="$comandas->total().' comandas registradas'">
            <x-slot name="actions">
                <a href="{{ route('admin.ventas.comandas.create') }}" class="admin-btn-primary">Nueva comanda</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.ventas.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.ventas.comandas.index') }}" class="admin-card mb-4 grid gap-3 p-4 lg:grid-cols-4">
        <div>
            <x-input-label for="busqueda" value="Numero, mesa o cliente" />
            <x-text-input id="busqueda" name="busqueda" class="mt-1 block h-10 w-full" :value="$filtros['busqueda']" maxlength="100" />
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
        <div class="flex items-end gap-2 lg:col-span-2">
            <button type="submit" class="admin-btn-primary">Filtrar</button>
            <a href="{{ route('admin.ventas.comandas.index') }}" class="admin-btn-outline">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Numero</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Mesa</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Lineas</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Ubicacion</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comandas as $comanda)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3 font-medium text-foreground">{{ $comanda->numero }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $comanda->mesa ?: '-' }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$comanda->estado->variante()">{{ $comanda->estado->etiqueta() }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $comanda->lineas_count }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $comanda->ubicacionInventario?->nombre ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-foreground">{{ number_format((float) $comanda->total, 2, ',', '.') }} EUR</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.ventas.comandas.show', $comanda) }}" class="text-primary hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay comandas todavia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $comandas->links() }}</div>
</x-app-layout>
