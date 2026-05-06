<x-publico-layout :title="$post->titulo.' | Cerveceria Europa'" :description="$post->resumen">
    <article class="bg-public-background">
        @if ($post->urlImagen())
            <figure class="h-[45vh] min-h-80">
                <img src="{{ $post->urlImagen() }}" alt="{{ $post->titulo }}" fetchpriority="high" class="h-full w-full object-cover">
                <figcaption class="sr-only">{{ $post->titulo }}</figcaption>
            </figure>
        @endif

        <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
            <header>
                <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">
                    @if ($post->publicado_at)
                        <time datetime="{{ $post->publicado_at->toDateString() }}">{{ $post->publicado_at->format('d/m/Y') }}</time>
                        <span aria-hidden="true">&middot;</span>
                    @endif
                    <span>{{ $post->autor ?: 'Cerveceria Europa' }}</span>
                </p>
                <h1 class="mt-4 text-4xl font-black text-public-foreground sm:text-5xl">{{ $post->titulo }}</h1>
            </header>

            @if ($post->categorias->isNotEmpty())
                <ul class="mt-5 flex flex-wrap gap-2">
                    @foreach ($post->categorias as $categoria)
                        <li><a href="{{ route('web.blog.categoria', $categoria) }}" class="rounded bg-public-primary/15 px-2 py-1 text-xs font-bold text-public-primary">{{ $categoria->nombre }}</a></li>
                    @endforeach
                </ul>
            @endif

            @if ($post->resumen)
                <p class="mt-5 text-xl leading-8 text-public-muted">{{ $post->resumen }}</p>
            @endif

            <section class="mt-10 whitespace-pre-line text-lg leading-8 text-public-foreground" aria-label="Contenido del articulo">{{ $post->contenido }}</section>
        </div>
    </article>
</x-publico-layout>
