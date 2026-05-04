<x-publico-layout title="Blog | Cerveceria Europa" description="Blog de Cerveceria Europa.">
    <section class="border-b border-public-border/15 bg-public-surface">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Modulo blog</p>
            <h1 class="mt-3 text-4xl font-black text-public-foreground sm:text-6xl">{{ $categoriaActual ? 'Blog: '.$categoriaActual->nombre : 'Blog' }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-public-muted">Noticias, eventos, cervezas invitadas y novedades del bar.</p>
            @if ($categorias->isNotEmpty())
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('web.blog') }}" class="rounded-md border border-public-border/20 px-3 py-2 text-sm font-bold {{ $categoriaActual ? 'text-public-muted' : 'bg-public-primary text-[#23180f]' }}">Todas</a>
                    @foreach ($categorias as $categoria)
                        <a href="{{ route('web.blog.categoria', $categoria) }}" class="rounded-md border border-public-border/20 px-3 py-2 text-sm font-bold {{ $categoriaActual?->id === $categoria->id ? 'bg-public-primary text-[#23180f]' : 'text-public-muted hover:text-public-primary' }}">{{ $categoria->nombre }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="bg-public-background py-12">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
            @forelse ($posts as $post)
                <article class="overflow-hidden rounded-lg border border-public-border/15 bg-public-surface">
                    @if ($post->urlImagen())
                        <img src="{{ $post->urlImagen() }}" alt="{{ $post->titulo }}" class="aspect-[4/3] w-full object-cover">
                    @endif
                    <div class="space-y-3 p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-public-primary">{{ $post->publicado_at?->format('d/m/Y') }}</p>
                        <h2 class="text-xl font-black text-public-foreground">{{ $post->titulo }}</h2>
                        @if ($post->categorias->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach ($post->categorias as $categoria)
                                    <span class="rounded bg-public-primary/15 px-2 py-1 text-xs font-bold text-public-primary">{{ $categoria->nombre }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if ($post->resumen)
                            <p class="text-sm leading-6 text-public-muted">{{ $post->resumen }}</p>
                        @endif
                        <a href="{{ route('web.blog.show', $post) }}" class="inline-flex text-sm font-bold text-public-primary hover:underline">Leer mas</a>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-public-border/15 bg-public-surface p-8 text-public-muted md:col-span-3">Todavia no hay posts publicados.</div>
            @endforelse
        </div>
        <div class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">{{ $posts->links() }}</div>
    </section>
</x-publico-layout>
