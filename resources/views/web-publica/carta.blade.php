<x-publico-layout title="Carta | Cerveceria Europa" description="Carta publica de Cerveceria Europa con platos y cervezas publicados desde el panel.">
    <section class="border-b border-public-border/15 bg-public-surface">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Carta viva</p>
            <h1 class="mt-3 text-4xl font-black text-public-foreground sm:text-6xl">Carta</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-public-muted">Carta organizada por secciones editables desde el panel. Si un producto vinculado al inventario se queda sin stock, desaparece automaticamente.</p>
        </div>
    </section>

    <section class="bg-public-background py-12">
        <div class="mx-auto max-w-7xl space-y-12 px-4 sm:px-6 lg:px-8">
            @if ($categoriasPadre->isNotEmpty())
                <nav class="flex flex-wrap gap-2" aria-label="Secciones de carta">
                    @foreach ($categoriasPadre as $categoriaPadre)
                        <a href="#{{ $categoriaPadre->slug }}" class="rounded-md border border-public-border/20 bg-public-surface px-3 py-2 text-sm font-bold text-public-foreground transition hover:border-public-primary hover:text-public-primary">{{ $categoriaPadre->nombre }}</a>
                    @endforeach
                </nav>
            @endif

            @forelse ($categoriasPadre as $categoriaPadre)
                <section id="{{ $categoriaPadre->slug }}" class="scroll-mt-24">
                    <div class="mb-6">
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-public-primary">Carta</p>
                        <h2 class="mt-2 text-3xl font-black text-public-foreground">{{ $categoriaPadre->nombre }}</h2>
                        @if ($categoriaPadre->descripcion)
                            <p class="mt-2 max-w-3xl text-public-muted">{{ $categoriaPadre->descripcion }}</p>
                        @endif
                    </div>

                    @if ($categoriaPadre->contenidos->isNotEmpty())
                        <div class="mb-8 space-y-3">
                            @foreach ($categoriaPadre->contenidos as $contenido)
                                <x-web-publica.fila-contenido :contenido="$contenido" />
                            @endforeach
                        </div>
                    @endif

                    <div class="space-y-10">
                        @forelse ($categoriaPadre->hijas as $categoriaHija)
                            <div>
                                <div class="mb-4 border-b border-public-border/15 pb-3">
                                    <h3 class="text-2xl font-black text-public-foreground">{{ $categoriaHija->nombre }}</h3>
                                    @if ($categoriaHija->descripcion)
                                        <p class="mt-1 text-public-muted">{{ $categoriaHija->descripcion }}</p>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    @forelse ($categoriaHija->contenidos as $contenido)
                                        <x-web-publica.fila-contenido :contenido="$contenido" />
                                    @empty
                                        <div class="rounded-lg border border-public-border/15 bg-public-surface p-8 text-public-muted md:col-span-3">Todavia no hay productos publicados en esta seccion.</div>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            @if ($categoriaPadre->contenidos->isEmpty())
                                <div class="rounded-lg border border-public-border/15 bg-public-surface p-8 text-public-muted">Todavia no hay productos publicados en esta categoria.</div>
                            @endif
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="rounded-lg border border-public-border/15 bg-public-surface p-8 text-public-muted">Todavia no hay categorias de carta publicadas.</div>
            @endforelse

            @if ($sinCategoria->isNotEmpty())
                <div>
                    <div class="mb-6">
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-public-primary">Sin clasificar</p>
                        <h2 class="mt-2 text-3xl font-black text-public-foreground">Otros productos</h2>
                    </div>
                    <div class="space-y-3">
                        @foreach ($sinCategoria as $contenido)
                            <x-web-publica.fila-contenido :contenido="$contenido" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-publico-layout>
