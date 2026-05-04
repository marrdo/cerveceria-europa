<x-publico-layout :title="$post->titulo.' | Cerveceria Europa'" :description="$post->resumen">
    <article class="bg-public-background">
        @if ($post->urlImagen())
            <div class="h-[45vh] min-h-80 bg-cover bg-center" style="background-image: url('{{ $post->urlImagen() }}')"></div>
        @endif
        <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">{{ $post->publicado_at?->format('d/m/Y') }} · {{ $post->autor ?: 'Cerveceria Europa' }}</p>
            <h1 class="mt-4 text-4xl font-black text-public-foreground sm:text-5xl">{{ $post->titulo }}</h1>
            @if ($post->categorias->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($post->categorias as $categoria)
                        <a href="{{ route('web.blog.categoria', $categoria) }}" class="rounded bg-public-primary/15 px-2 py-1 text-xs font-bold text-public-primary">{{ $categoria->nombre }}</a>
                    @endforeach
                </div>
            @endif
            @if ($post->resumen)
                <p class="mt-5 text-xl leading-8 text-public-muted">{{ $post->resumen }}</p>
            @endif
            <div class="mt-10 whitespace-pre-line text-lg leading-8 text-public-foreground">{{ $post->contenido }}</div>
        </div>
    </article>
</x-publico-layout>
