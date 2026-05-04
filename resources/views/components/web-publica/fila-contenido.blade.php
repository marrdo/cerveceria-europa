@props(['contenido'])

<article class="rounded-lg border border-public-border/15 bg-public-surface p-4 shadow-lg shadow-black/5">
    <div class="flex gap-4">
        @if ($contenido->urlImagen())
            <img src="{{ $contenido->urlImagen() }}" alt="{{ $contenido->titulo }}" class="h-20 w-20 shrink-0 rounded-md object-cover">
        @endif

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h4 class="text-base font-black text-public-foreground">{{ $contenido->titulo }}</h4>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @if ($contenido->fuera_carta)
                            <span class="rounded bg-public-primary px-2 py-0.5 text-[11px] font-black uppercase text-[#23180f]">Fuera de carta</span>
                        @endif
                        @if ($contenido->destacado)
                            <span class="rounded bg-[#1f5b45] px-2 py-0.5 text-[11px] font-black uppercase text-white">Destacado</span>
                        @endif
                    </div>
                </div>

                <div class="min-w-[120px] shrink-0 text-right">
                    @if ($contenido->tieneTarifas())
                        <div class="space-y-1">
                            @foreach ($contenido->tarifas as $tarifa)
                                <div class="flex justify-between gap-3 text-sm">
                                    <span class="text-public-muted">{{ $tarifa->nombre ?: 'Precio' }}</span>
                                    <span class="font-black text-public-foreground">{{ $tarifa->precioFormateado() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($contenido->precioFormateado())
                        <span class="rounded bg-[#f4dfb8] px-2 py-1 text-sm font-bold text-[#23180f]">{{ $contenido->precioFormateado() }}</span>
                    @endif
                </div>
            </div>

            @if ($contenido->descripcion_corta)
                <p class="mt-2 text-sm leading-6 text-public-muted">{{ $contenido->descripcion_corta }}</p>
            @endif

            @if ($contenido->alergenos)
                <p class="mt-2 text-xs text-public-muted/80">Alergenos: {{ implode(', ', $contenido->alergenos) }}</p>
            @endif
        </div>
    </div>
</article>
