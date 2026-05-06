@props(['contenido'])

<article class="group overflow-hidden rounded-lg border border-public-border/15 bg-public-surface shadow-xl shadow-black/10">
    <figure class="relative aspect-[4/3] overflow-hidden bg-[#3b2a1f]">
        @if ($contenido->urlImagen())
            <img src="{{ $contenido->urlImagen() }}" alt="{{ $contenido->titulo }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            <figcaption class="sr-only">{{ $contenido->titulo }}</figcaption>
        @else
            <figcaption class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_35%_20%,rgba(227,161,58,.45),transparent_28%),linear-gradient(135deg,#3b2a1f,#17110d)]" aria-label="{{ $contenido->titulo }}">
                <x-brand.beer-icon class="h-16 w-16 text-[#e3a13a]" />
            </figcaption>
        @endif
        @if ($contenido->fuera_carta || $contenido->destacado)
            <ul class="absolute left-3 top-3 flex flex-wrap gap-2">
                @if ($contenido->fuera_carta)
                    <li class="rounded bg-public-primary px-2 py-1 text-xs font-black uppercase text-[#23180f]">Fuera de carta</li>
                @endif
                @if ($contenido->destacado)
                    <li class="rounded bg-[#1f5b45] px-2 py-1 text-xs font-black uppercase text-white">Destacado</li>
                @endif
            </ul>
        @endif
    </figure>
    <section class="space-y-3 p-4" aria-label="{{ $contenido->titulo }}">
        <header class="flex items-start justify-between gap-4">
            <h3 class="text-lg font-black text-public-foreground">{{ $contenido->titulo }}</h3>
            @if (! $contenido->tieneTarifas() && $contenido->precioFormateado())
                <span class="shrink-0 rounded bg-[#f4dfb8] px-2 py-1 text-sm font-bold text-[#23180f]">{{ $contenido->precioFormateado() }}</span>
            @endif
        </header>
        @if ($contenido->tieneTarifas())
            <dl class="space-y-1 rounded-md border border-public-border/15 bg-public-background/60 p-2">
                @foreach ($contenido->tarifas as $tarifa)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <dt class="text-public-muted">{{ $tarifa->nombre ?: 'Precio' }}</dt>
                        <dd class="font-black text-public-foreground">{{ $tarifa->precioFormateado() }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
        @if ($contenido->descripcion_corta)
            <p class="text-sm leading-6 text-public-muted">{{ $contenido->descripcion_corta }}</p>
        @endif
        @if ($contenido->alergenos)
            <p class="text-xs text-public-muted/80">Alergenos: {{ implode(', ', $contenido->alergenos) }}</p>
        @endif
    </section>
</article>
