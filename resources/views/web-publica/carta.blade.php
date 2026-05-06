<x-publico-layout title="Carta | Cerveceria Europa" description="Carta publica de Cerveceria Europa con platos y cervezas publicados desde el panel.">
    <section class="border-b border-public-border/15 bg-public-surface">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Carta viva</p>
            <h1 class="mt-3 text-4xl font-black text-public-foreground sm:text-6xl">Carta</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-public-muted">Carta organizada por secciones editables desde el panel. Si un producto vinculado al inventario se queda sin stock, desaparece automaticamente.</p>
        </div>
    </section>

    <section class="bg-public-background py-12">
        <div
            class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8"
            x-data="{ seccionActiva: '{{ $categoriasPadre->first()?->slug }}' }"
        >
            @if ($categoriasPadre->isNotEmpty())
                <div class="sticky top-[73px] z-30 border-y border-public-border/15 bg-public-background/95 py-3 backdrop-blur">
                    <nav class="flex gap-2 overflow-x-auto" aria-label="Secciones principales de carta">
                        @foreach ($categoriasPadre as $categoriaPadre)
                            @php
                                $totalCategoria = $categoriaPadre->contenidos->count()
                                    + $categoriaPadre->hijas->sum(fn ($hija) => $hija->contenidos->count());
                            @endphp
                            <button
                                type="button"
                                class="flex shrink-0 items-center gap-2 rounded-md border px-4 py-2 text-sm font-black uppercase tracking-wide transition"
                                :class="seccionActiva === '{{ $categoriaPadre->slug }}' ? 'border-public-primary bg-public-primary text-[#23180f]' : 'border-public-border/20 bg-public-surface text-public-foreground hover:border-public-primary hover:text-public-primary'"
                                @click="seccionActiva = '{{ $categoriaPadre->slug }}'"
                                title="Ver {{ $categoriaPadre->nombre }}"
                                aria-controls="seccion-{{ $categoriaPadre->slug }}"
                            >
                                <span>{{ $categoriaPadre->nombre }}</span>
                                <span class="rounded bg-public-background/70 px-2 py-0.5 text-xs text-public-muted">{{ $totalCategoria }}</span>
                            </button>
                        @endforeach
                    </nav>
                </div>
            @endif

            @forelse ($categoriasPadre as $categoriaPadre)
                <section
                    id="seccion-{{ $categoriaPadre->slug }}"
                    class="scroll-mt-32"
                    x-show="seccionActiva === '{{ $categoriaPadre->slug }}'"
                    x-cloak
                >
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

                    <div class="space-y-3">
                        @forelse ($categoriaPadre->hijas as $categoriaHija)
                            @if ($categoriaHija->contenidos->isNotEmpty())
                                <div x-data="{ abierta: false }" class="overflow-hidden rounded-lg border border-public-border/15 bg-public-surface">
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-4 px-4 py-4 text-left transition hover:bg-public-background/60"
                                        @click="abierta = ! abierta"
                                        :aria-expanded="abierta"
                                        title="Abrir o cerrar {{ $categoriaHija->nombre }}"
                                    >
                                        <span class="min-w-0">
                                            <span class="block text-lg font-black text-public-foreground">{{ $categoriaHija->nombre }}</span>
                                            @if ($categoriaHija->descripcion)
                                                <span class="mt-1 block text-sm text-public-muted">{{ $categoriaHija->descripcion }}</span>
                                            @endif
                                        </span>
                                        <span class="flex shrink-0 items-center gap-3">
                                            <span class="rounded-md border border-public-border/20 bg-public-background px-2 py-1 text-xs font-black text-public-muted">{{ $categoriaHija->contenidos->count() }}</span>
                                            <svg class="h-5 w-5 text-public-primary transition" :class="abierta ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                                            </svg>
                                        </span>
                                    </button>

                                    <div x-show="abierta" x-transition class="space-y-3 border-t border-public-border/15 bg-public-background/35 p-3">
                                        @foreach ($categoriaHija->contenidos as $contenido)
                                            <x-web-publica.fila-contenido :contenido="$contenido" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif
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
