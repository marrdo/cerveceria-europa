<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Categorias del blog" description="Secciones editoriales para organizar posts.">
            <x-slot name="actions">
                <a href="{{ route('admin.web-publica.blog-categorias.create') }}" class="admin-btn-primary">Nueva categoria</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Categoria</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Orden</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categorias as $categoria)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">{{ $categoria->nombre }}</div>
                            <div class="text-xs text-muted-foreground">{{ $categoria->descripcion ?: 'Sin descripcion' }}</div>
                        </td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$categoria->activo ? 'success' : 'default'">{{ $categoria->activo ? 'Activa' : 'Oculta' }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $categoria->orden }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.web-publica.blog-categorias.edit', $categoria) }}" class="text-primary hover:underline">Editar</a>
                            <form method="POST" action="{{ route('admin.web-publica.blog-categorias.destroy', $categoria) }}" class="inline" onsubmit="return confirm('Eliminar esta categoria?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ms-3 text-destructive hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">Todavia no hay categorias.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categorias->links() }}</div>
</x-app-layout>
