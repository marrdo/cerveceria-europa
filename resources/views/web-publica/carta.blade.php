<x-publico-layout title="Carta | Cerveceria Europa" description="Carta publica de Cerveceria Europa con platos y cervezas publicados desde el panel.">
    {{-- Page header rompedor: una sola palabra a 18vw --}}
    <section class="relative overflow-hidden border-b px-8 pb-14 pt-20" style="border-color: var(--v2-line);">
        <div class="mx-auto max-w-[1440px]">
            <div class="font-mono text-[13px] font-medium tracking-wider text-amber-bright">
                / Carta viva · {{ $categoriasPadre->sum(fn ($c) => $c->contenidos->count() + $c->hijas->sum(fn ($h) => $h->contenidos->count())) }} referencias publicadas
            </div>
            <h1 class="relative z-[2] m-0 font-display leading-[0.82] tracking-[-0.01em] text-ink" style="font-size: clamp(5rem, 18vw, 22rem);">Carta.</h1>
            <p class="relative z-[2] mt-8 max-w-[56ch] text-lg leading-[1.5] text-ink-mute">
                Organizada por secciones editables desde el panel. Si un producto vinculado al inventario se queda sin stock, desaparece automaticamente. Si vuelve, vuelve.
            </p>
        </div>
    </section>

    {{-- Tap list completa --}}
    <section class="px-8 pb-24 pt-14">
        <div class="mx-auto max-w-[1440px]" x-data="{ activa: '{{ $categoriasPadre->first()?->slug }}' }">
            <div class="grid grid-cols-1 gap-16 lg:grid-cols-[minmax(280px,1fr)_2fr]">
                {{-- Aside sticky con categorias numeradas --}}
                <aside class="flex flex-col gap-1 lg:sticky lg:top-22 lg:self-start" style="top: 88px;" aria-label="Categorias de carta">
                    @foreach ($categoriasPadre as $i => $cat)
                        @php
                            $total = $cat->contenidos->count() + $cat->hijas->sum(fn ($h) => $h->contenidos->count());
                        @endphp
                        <button
                            type="button"
                            class="v2-tap-cat"
                            :class="activa === '{{ $cat->slug }}' ? 'on' : ''"
                            @click="activa = '{{ $cat->slug }}'">
                            <span>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} · {{ $cat->nombre }}</span>
                            <span class="n">{{ $total }}</span>
                        </button>
                    @endforeach
                </aside>

                {{-- Listas de filas por categoria --}}
                <div>
                    @foreach ($categoriasPadre as $cat)
                        <div x-show="activa === '{{ $cat->slug }}'" x-cloak class="flex flex-col">
                            @php $idx = 1; @endphp
                            @foreach ($cat->contenidos as $c)
                                <x-web-publica.tap-row :contenido="$c" :index="$idx++" />
                            @endforeach
                            @foreach ($cat->hijas as $hija)
                                @if ($hija->contenidos->isNotEmpty())
                                    <div class="mb-2 mt-10 border-b pb-3" style="border-color: var(--v2-line);">
                                        <p class="font-mono text-xs uppercase tracking-[0.16em] text-amber-bright">— {{ $hija->nombre }}</p>
                                    </div>
                                    @foreach ($hija->contenidos as $c)
                                        <x-web-publica.tap-row :contenido="$c" :index="$idx++" />
                                    @endforeach
                                @endif
                            @endforeach

                            @if ($cat->contenidos->isEmpty() && $cat->hijas->every(fn ($h) => $h->contenidos->isEmpty()))
                                <div class="rounded-3xl border border-dashed p-20 text-center text-base text-ink-mute" style="border-color: var(--v2-line-2);">
                                    Todavia no hay productos publicados en esta categoria.
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if ($sinCategoria->isNotEmpty())
                        <div class="mt-16">
                            <p class="font-mono text-xs uppercase tracking-[0.16em] text-amber-bright">— Otros productos</p>
                            <div class="mt-4 flex flex-col">
                                @foreach ($sinCategoria as $i => $c)
                                    <x-web-publica.tap-row :contenido="$c" :index="$i + 1" />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-publico-layout>
