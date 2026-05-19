@props([
    'contenido' => null,
    'index'     => 1,
])

@php
    $titulo      = $contenido->titulo;
    $desc        = $contenido->descripcion_corta;
    $alergenos   = $contenido->alergenos;
    $tieneTarifas= $contenido->tieneTarifas();
@endphp

<article class="v2-tap-row">
    <div class="v2-tap-num">{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}</div>

    <div class="flex flex-col gap-1.5">
        <div class="flex flex-wrap items-baseline gap-3.5">
            <h3 class="v2-tap-name">{{ $titulo }}</h3>

            @if ($contenido->destacado)
                <span class="v2-tap-tag hops">Destacado</span>
            @endif
            @if ($contenido->fuera_carta)
                <span class="v2-tap-tag amber">Fuera de carta</span>
            @endif
            @foreach (($contenido->tags ?? []) as $t)
                <span class="v2-tap-tag">{{ $t }}</span>
            @endforeach
        </div>

        @if ($desc)
            <p class="m-0 max-w-[56ch] text-sm leading-[1.55] text-ink-mute">{{ $desc }}</p>
        @endif

        @if ($alergenos)
            <p class="m-0 text-[11px] italic text-ink-mute/80">Alergenos: {{ implode(', ', $alergenos) }}</p>
        @endif
    </div>

    <div class="flex flex-col items-end gap-1.5 text-right">
        @if ($tieneTarifas)
            @foreach ($contenido->tarifas as $tar)
                <div class="flex items-baseline gap-2.5">
                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-ink-mute">{{ $tar->nombre ?: 'Precio' }}</span>
                    <b class="font-mono text-sm font-bold tabular-nums text-ink">{{ $tar->precioFormateado() }}</b>
                </div>
            @endforeach
        @elseif ($contenido->precioFormateado())
            <div class="font-mono text-[22px] font-bold tabular-nums text-ink">{{ $contenido->precioFormateado() }}</div>
        @endif
    </div>
</article>
