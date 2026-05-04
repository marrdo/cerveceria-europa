<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Secciones web" description="Textos estructurales editables de la web publica." />
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Seccion</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Titulo</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($secciones as $seccion)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">{{ $seccion->nombre }}</div>
                            <div class="text-xs text-muted-foreground">{{ $seccion->clave }}</div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $seccion->titulo ?: '-' }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$seccion->activo ? 'success' : 'default'">{{ $seccion->activo ? 'Activa' : 'Oculta' }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.web-publica.secciones.edit', $seccion) }}" class="text-primary hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">Todavia no hay secciones configuradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
