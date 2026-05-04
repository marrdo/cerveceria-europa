<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Blog" description="Posts publicados en el modulo opcional de blog.">
            <x-slot name="actions">
                <a href="{{ route('admin.web-publica.blog.create') }}" class="admin-btn-primary">Nuevo post</a>
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
                    <th class="px-4 py-3 text-left font-medium text-foreground">Post</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Autor</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estados</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Publicado</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">{{ $post->titulo }}</div>
                            <div class="text-xs text-muted-foreground">{{ $post->resumen ?: 'Sin resumen' }}</div>
                            @if ($post->categorias->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($post->categorias as $categoria)
                                        <x-admin.status-badge variant="info">{{ $categoria->nombre }}</x-admin.status-badge>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $post->autor ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.web-publica.blog.toggle', [$post, 'publicado']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"><x-admin.status-badge :variant="$post->publicado ? 'success' : 'default'">{{ $post->publicado ? 'Publicado' : 'Oculto' }}</x-admin.status-badge></button>
                                </form>
                                <form method="POST" action="{{ route('admin.web-publica.blog.toggle', [$post, 'destacado']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"><x-admin.status-badge :variant="$post->destacado ? 'warning' : 'default'">{{ $post->destacado ? 'Destacado' : 'Normal' }}</x-admin.status-badge></button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $post->publicado_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.web-publica.blog.edit', $post) }}" class="text-primary hover:underline">Editar</a>
                            <form method="POST" action="{{ route('admin.web-publica.blog.destroy', $post) }}" class="inline" onsubmit="return confirm('Eliminar este post?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ms-3 text-destructive hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">Todavia no hay posts.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
</x-app-layout>
