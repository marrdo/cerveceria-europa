<x-publico-layout :title="$titulo.' | Cerveceria Europa'" :description="$descripcion">
    <section class="border-b border-public-border/15 bg-public-surface">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Cerveceria Europa</p>
            <h1 class="mt-3 text-4xl font-black text-public-foreground sm:text-6xl">{{ $titulo }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-public-muted">{{ $descripcion }}</p>
        </div>
    </section>

    <section class="bg-public-background py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 md:grid-cols-3">
                @forelse ($contenidos as $contenido)
                    <x-web-publica.tarjeta-contenido :contenido="$contenido" />
                @empty
                    <div class="rounded-lg border border-public-border/15 bg-public-surface p-8 text-public-muted md:col-span-3">Todavia no hay contenido publicado en esta seccion.</div>
                @endforelse
            </div>
            <div class="mt-8">{{ $contenidos->links() }}</div>
        </div>
    </section>
</x-publico-layout>
