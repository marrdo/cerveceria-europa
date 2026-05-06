<x-publico-layout title="Cerveceria Europa">
    <section class="relative overflow-hidden">
        <figure class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1518176258769-f227c798150e?auto=format&fit=crop&w=1800&q=80" alt="Interior de bar con cerveza servida" class="h-full w-full object-cover" fetchpriority="high">
            <figcaption class="sr-only">Interior de bar con cerveza servida</figcaption>
        </figure>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(24,19,15,.95),rgba(24,19,15,.72),rgba(24,19,15,.35))]"></div>

        <div class="relative mx-auto grid min-h-[calc(100vh-73px)] max-w-7xl content-center px-4 py-20 sm:px-6 lg:px-8">
            <header class="max-w-3xl">
                <p class="text-sm font-black uppercase tracking-[0.26em] text-[#e3a13a]">Sevilla &middot; cerveza &middot; cocina</p>
                <h1 class="mt-5 max-w-2xl text-5xl font-black leading-none text-white sm:text-7xl">Cerveceria Europa</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-[#ead8b9]">Bar con alma industrial, seleccion de cervezas de importacion y artesanas, y cocina pensada para maridar sin complicaciones.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('web.carta') }}" class="rounded-md bg-[#d08a24] px-5 py-3 text-sm font-black uppercase text-[#23180f] hover:bg-[#e3a13a]">Ver carta</a>
                    <a href="{{ route('web.recomendaciones') }}" class="rounded-md border border-white/20 px-5 py-3 text-sm font-black uppercase text-white hover:border-[#e3a13a] hover:text-[#e3a13a]">Recomendaciones</a>
                </div>
            </header>
        </div>
    </section>

    @if ($fueraCarta->isNotEmpty())
        <section class="bg-public-background py-14 text-public-foreground" aria-labelledby="fuera-carta-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <header class="mb-8 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Hoy en barra</p>
                        <h2 id="fuera-carta-heading" class="mt-2 text-3xl font-black">Fuera de carta</h2>
                    </div>
                    <a href="{{ route('web.fuera-carta') }}" class="text-sm font-bold text-public-primary hover:underline">Ver todo</a>
                </header>
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ($fueraCarta as $contenido)
                        <x-web-publica.tarjeta-contenido :contenido="$contenido" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-public-background py-14" aria-labelledby="destacados-heading">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_1.2fr] lg:px-8">
            <header>
                <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Especialidad</p>
                <h2 id="destacados-heading" class="mt-2 text-3xl font-black text-public-foreground">Cervezas para descubrir y platos para compartir</h2>
                <p class="mt-4 text-public-muted">Una carta pensada para probar estilos distintos, pedir algo al centro y encontrar novedades segun temporada.</p>
            </header>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($destacados as $contenido)
                    <x-web-publica.tarjeta-contenido :contenido="$contenido" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-public-surface py-14" aria-labelledby="experiencia-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="max-w-2xl">
                <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">En el local</p>
                <h2 id="experiencia-heading" class="mt-2 text-3xl font-black text-public-foreground">Cerveza, cocina y novedades sin perder tiempo buscando</h2>
            </header>
            <ol class="mt-8 grid gap-5 md:grid-cols-3">
                <li>
                    <article class="h-full rounded-lg border border-public-border/15 p-6">
                        <p class="text-3xl font-black text-public-primary">01</p>
                        <h3 class="mt-4 font-black text-public-foreground">Cervezas por estilo</h3>
                        <p class="mt-2 text-sm text-public-muted">Importacion, artesanas, tiradores, sin alcohol y novedades organizadas para elegir rapido.</p>
                    </article>
                </li>
                <li>
                    <article class="h-full rounded-lg border border-public-border/15 p-6">
                        <p class="text-3xl font-black text-public-primary">02</p>
                        <h3 class="mt-4 font-black text-public-foreground">Cocina para compartir</h3>
                        <p class="mt-2 text-sm text-public-muted">Platos frios, elaboraciones calientes y fuera de carta pensados para acompanar la cerveza.</p>
                    </article>
                </li>
                <li>
                    <article class="h-full rounded-lg border border-public-border/15 p-6">
                        <p class="text-3xl font-black text-public-primary">03</p>
                        <h3 class="mt-4 font-black text-public-foreground">Recomendaciones del bar</h3>
                        <p class="mt-2 text-sm text-public-muted">Selecciones destacadas para descubrir referencias nuevas o pedir algo especial de temporada.</p>
                    </article>
                </li>
            </ol>
        </div>
    </section>
</x-publico-layout>
