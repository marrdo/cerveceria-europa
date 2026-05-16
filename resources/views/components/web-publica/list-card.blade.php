@props([
    'contenido' => null,
])

@php
    $imagen    = $contenido->urlImagen();
    $titulo    = $contenido->titulo;
    $desc      = $contenido->descripcion_corta;
    $precio    = $contenido->precioFormateado();
    $tags      = [];
    if ($contenido->destacado)    $tags[] = ['label' => 'Destacado', 'tone' => 'hops'];
    if ($contenido->fuera_carta)  $tags[] = ['label' => 'Fuera de carta', 'tone' => 'amber'];
    if (optional($contenido->categoria)->nombre) {
        $tags[] = ['label' => $contenido->categoria->nombre, 'tone' => ''];
    }
@endphp

<article class="v2-card">
    <div class="v2-card-img {{ $imagen ? '' : 'empty' }}" @if ($imagen) style="background-image: url('{{ $imagen }}');" @endif>
        @if (! $imagen)
            <div class="flex h-full w-full items-center justify-center">
                <x-brand.beer-icon class="h-12 w-12 text-amber-bright" />
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-2 px-5 py-5">
        @if (count($tags) > 0)
            <ul class="m-0 flex flex-wrap gap-1.5 p-0">
                @foreach ($tags as $t)
                    <li class="v2-tap-tag {{ $t['tone'] }}">{{ $t['label'] }}</li>
                @endforeach
            </ul>
        @endif

        <div class="flex items-start justify-between gap-4">
            <h3 class="m-0 font-display text-[26px] leading-none tracking-[0.005em] text-ink">{{ $titulo }}</h3>
            @if ($precio)
                <span class="font-mono text-lg font-bold tabular-nums text-amber-bright">{{ $precio }}</span>
            @endif
        </div>

        @if ($desc)
            <p class="m-0 flex-1 text-[13px] leading-[1.55] text-ink-mute">{{ $desc }}</p>
        @endif

        @if ($contenido->alergenos)
            <p class="m-0 mt-2 text-[11px] italic text-ink-mute/80">Alergenos: {{ implode(', ', $contenido->alergenos) }}</p>
        @endif
    </div>
</article>
