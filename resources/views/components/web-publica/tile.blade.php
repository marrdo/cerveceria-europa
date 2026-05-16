@props([
    'contenido' => null,    // ContenidoWeb (preferido)
    'cat'       => null,    // override de etiqueta de categoria
    'titulo'    => null,
    'descripcion'=> null,
    'precio'    => null,    // string formateado
    'imagen'    => null,    // url
    'sticker'   => null,    // ['label'=>'Hoy','price'=>'4,80 €'] - opcional
    // span helpers — clases utiles para el bento
    'cols'      => 'col-span-12 lg:col-span-4',
    'rows'      => '',
])

@php
    if ($contenido) {
        $titulo      = $titulo      ?? $contenido->titulo;
        $descripcion = $descripcion ?? $contenido->descripcion_corta;
        $precio      = $precio      ?? $contenido->precioFormateado();
        $imagen      = $imagen      ?? $contenido->urlImagen();
        $cat         = $cat         ?? optional($contenido->categoria)->nombre;
    }
@endphp

<article {{ $attributes->merge(['class' => "v2-tile $cols $rows"]) }}>
    @if ($imagen)
        <div class="v2-tile-img" style="background-image: url('{{ $imagen }}');"></div>
        <div class="v2-tile-fade"></div>
    @endif

    @if ($sticker)
        <div class="v2-sticker">
            <div>
                @if (!empty($sticker['label']))
                    <div class="text-[9px] uppercase tracking-[0.16em]">{{ $sticker['label'] }}</div>
                @endif
                @if (!empty($sticker['price']))
                    <div class="font-mono text-lg font-bold">{{ $sticker['price'] }}</div>
                @endif
            </div>
        </div>
    @endif

    @if (! $imagen && ! $titulo)
        {{-- Tile vacio: usuario rellena el contenido como hijo --}}
        {{ $slot }}
    @else
        <div class="absolute inset-x-0 bottom-0 z-[2] flex flex-col gap-1 px-6 py-5">
            @if ($cat)
                <div class="text-[10.5px] font-bold uppercase tracking-[0.18em] text-amber-bright">{{ $cat }}</div>
            @endif
            @if ($titulo)
                <h3 class="m-0 mt-1 font-display tracking-[0.005em] text-ink" style="font-size: clamp(1.6rem, 2.4vw, 2.4rem); line-height: 0.95;">{{ $titulo }}</h3>
            @endif
            @if ($descripcion)
                <p class="mt-2 max-w-[38ch] text-[13px] leading-[1.5] text-ink-mute">{{ $descripcion }}</p>
            @endif
            @if ($precio && ! $sticker)
                <div class="mt-3 flex items-end justify-between gap-3">
                    <span class="font-mono text-[22px] font-bold tabular-nums text-ink">{{ $precio }}</span>
                </div>
            @endif
        </div>
    @endif
</article>
