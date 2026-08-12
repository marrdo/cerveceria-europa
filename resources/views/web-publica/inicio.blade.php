<x-publico-layout>
    @if ($secciones['hero']->activo)
        <x-web-publica.hero :negocio="$negocio" :seccion="$secciones['hero']" :stats="$metricas" />
    @endif

    <x-web-publica.ticker :items="$fueraCarta" />

    @if ($secciones['sugerencias']->activo && $fueraCarta->isNotEmpty())
        <section class="px-8 py-24">
            <div class="mx-auto max-w-[1440px]">
                <x-web-publica.section-head num="01 / Sugerencias" :eyebrow="$secciones['sugerencias']->subtitulo" :titulo="$secciones['sugerencias']->titulo ?: 'Lo que recomendamos hoy'" :accion="['label' => 'Ver todo', 'href' => route('web.fuera-carta')]" />
                <div class="grid auto-rows-[280px] grid-cols-2 gap-4 md:grid-cols-6 lg:grid-cols-12">
                    @php
                        $tiles = $fueraCarta->take(6);
                        $first = $tiles->shift();
                    @endphp
                    @if ($first)
                        <x-web-publica.tile :contenido="$first" cols="col-span-2 md:col-span-6 lg:col-span-8" rows="row-span-2" :sticker="['label' => 'Hoy', 'price' => $first->precioFormateado()]" />
                    @endif
                    @foreach ($tiles as $contenido)
                        <x-web-publica.tile :contenido="$contenido" cols="col-span-2 md:col-span-3 lg:col-span-4" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($secciones['destacados']->activo && $destacados->isNotEmpty())
        <section class="px-8 pb-24">
            <div class="mx-auto max-w-[1440px]">
                <x-web-publica.section-head num="02 / Favoritos" :eyebrow="$secciones['destacados']->subtitulo" :titulo="$secciones['destacados']->titulo ?: 'Nuestra selección destacada'" :accion="['label' => 'Ver carta completa', 'href' => route('web.carta')]" />
                <div class="mx-auto max-w-[960px]">
                    @foreach ($destacados->take(4) as $indice => $contenido)
                        <x-web-publica.tap-row :contenido="$contenido" :index="$indice + 1" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($secciones['valores']->activo)
        @php($valores = $secciones['valores']->datos ?? [])
        <section class="px-8 py-16">
            <div class="mx-auto max-w-[1440px]">
                <x-web-publica.section-head num="03 / El local" :eyebrow="$secciones['valores']->subtitulo" :titulo="$secciones['valores']->titulo ?: 'Así trabajamos'" />
                <div class="grid grid-cols-1 border-t md:grid-cols-3" style="border-color: var(--v2-line);">
                    @foreach (['servicio', 'hops', 'star'] as $indice => $icono)
                        @php($numero = $indice + 1)
                        <x-web-publica.stripe :num="str_pad((string) $numero, 2, '0', STR_PAD_LEFT)" :titulo="$valores['valor_'.$numero.'_titulo'] ?? 'Nuestro valor'" :desc="$valores['valor_'.$numero.'_descripcion'] ?? 'Contenido editable desde el panel.'" :icono="$icono" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-publico-layout>
