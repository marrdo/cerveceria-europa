<x-publico-layout :title="$titulo.' | Cerveceria Europa'" :description="$descripcion">
    <section class="border-b border-public-border/15 bg-public-surface" aria-labelledby="listado-heading">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Cerveceria Europa</p>
            <h1 id="listado-heading" class="mt-3 text-4xl font-black text-public-foreground sm:text-6xl">{{ $titulo }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-public-muted">{{ $descripcion }}</p>
        </div>
    </section>

    <section class="bg-public-background py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($contenidos->isNotEmpty())
                <ul class="grid gap-5 md:grid-cols-3">
                    @foreach ($contenidos as $contenido)
                        <li>
                            <x-web-publica.tarjeta-contenido :contenido="$contenido" />
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="rounded-lg border border-public-border/15 bg-public-surface p-8 text-public-muted">Todavia no hay contenido publicado en esta seccion.</p>
            @endif
            <div class="mt-8">{{ $contenidos->links() }}</div>
        </div>
    </section>
</x-publico-layout>
