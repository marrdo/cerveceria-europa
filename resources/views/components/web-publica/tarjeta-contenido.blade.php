@props(['contenido'])

<article class="group overflow-hidden rounded-lg border border-public-border/15 bg-public-surface shadow-xl shadow-black/10">
    <div class="relative aspect-[4/3] overflow-hidden bg-[#3b2a1f]">
        @if ($contenido->urlImagen())
            <img src="{{ $contenido->urlImagen() }}" alt="{{ $contenido->titulo }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_35%_20%,rgba(227,161,58,.45),transparent_28%),linear-gradient(135deg,#3b2a1f,#17110d)]">
                <x-brand.beer-icon class="h-16 w-16 text-[#e3a13a]" />
            </div>
        @endif
        <div class="absolute left-3 top-3 flex flex-wrap gap-2">
            @if ($contenido->fuera_carta)
                <span class="rounded bg-public-primary px-2 py-1 text-xs font-black uppercase text-[#23180f]">Fuera de carta</span>
            @endif
            @if ($contenido->destacado)
                <span class="rounded bg-[#1f5b45] px-2 py-1 text-xs font-black uppercase text-white">Destacado</span>
            @endif
        </div>
    </div>
    <div class="space-y-3 p-4">
        <div class="flex items-start justify-between gap-4">
            <h3 class="text-lg font-black text-public-foreground">{{ $contenido->titulo }}</h3>
            @if (! $contenido->tieneTarifas() && $contenido->precioFormateado())
                <span class="shrink-0 rounded bg-[#f4dfb8] px-2 py-1 text-sm font-bold text-[#23180f]">{{ $contenido->precioFormateado() }}</span>
            @endif
        </div>
        @if ($contenido->tieneTarifas())
            <div class="space-y-1 rounded-md border border-public-border/15 bg-public-background/60 p-2">
                @foreach ($contenido->tarifas as $tarifa)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-public-muted">{{ $tarifa->nombre ?: 'Precio' }}</span>
                        <span class="font-black text-public-foreground">{{ $tarifa->precioFormateado() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        @if ($contenido->descripcion_corta)
            <p class="text-sm leading-6 text-public-muted">{{ $contenido->descripcion_corta }}</p>
        @endif
        @if ($contenido->alergenos)
            <p class="text-xs text-public-muted/80">Alergenos: {{ implode(', ', $contenido->alergenos) }}</p>
        @endif
    </div>
</article>
