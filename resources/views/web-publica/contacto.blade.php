<x-publico-layout title="Contacto | Cerveceria Europa">
    <section class="bg-public-background py-16" aria-labelledby="contacto-heading">
        @php($datos = $seccion->datos ?? [])

        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <article>
                <header>
                    <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Contacto</p>
                    <h1 id="contacto-heading" class="mt-3 text-5xl font-black text-public-foreground">{{ $seccion->titulo ?: 'Ven a Cerveceria Europa' }}</h1>
                    <p class="mt-5 text-lg leading-8 text-public-muted">{{ $seccion->subtitulo ?: 'Cervezas de importacion, artesanas y cocina de bar para compartir.' }}</p>
                </header>

                @if ($seccion->contenido)
                    <p class="mt-4 text-public-muted">{{ $seccion->contenido }}</p>
                @endif

                <address class="mt-8 not-italic text-public-muted">
                    <dl class="space-y-4">
                        <div>
                            <dt class="font-bold text-public-foreground">Ubicacion</dt>
                            <dd class="mt-1">{{ $datos['ubicacion'] ?? 'Sevilla' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-public-foreground">Reservas</dt>
                            <dd class="mt-1">{{ $datos['reservas'] ?? 'pendiente de configurar' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-public-foreground">Horario</dt>
                            <dd class="mt-1">{{ $datos['horario'] ?? 'pendiente de configurar' }}</dd>
                        </div>
                    </dl>
                </address>
            </article>

            <figure class="overflow-hidden rounded-lg border border-public-border/15 shadow-2xl shadow-black/30">
                <img src="https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=1200&q=80" alt="Barra de bar con servicio de cafe y bebidas" loading="lazy" decoding="async" class="h-full min-h-96 w-full object-cover">
                <figcaption class="sr-only">Ambiente de barra en Cerveceria Europa</figcaption>
            </figure>
        </div>
    </section>
</x-publico-layout>
