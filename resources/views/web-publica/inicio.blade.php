<x-publico-layout title="Cerveceria Europa">
    <x-web-publica.hero />

    <x-web-publica.ticker :items="$fueraCarta" />

    {{-- 01 / Hoy en barra — bento --}}
    <section class="px-8 py-24">
        <div class="mx-auto max-w-[1440px]">
            <x-web-publica.section-head
                num="01 / Hoy en barra"
                eyebrow="Fuera de carta · rotacion diaria"
                titulo="Lo que <em class='not-italic text-amber-bright'>pediriamos hoy</em>."
                :accion="['label' => 'Ver todo', 'href' => route('web.fuera-carta')]" />

            <div class="grid auto-rows-[280px] grid-cols-2 gap-4 md:grid-cols-6 lg:grid-cols-12">
                @php
                    $tiles = $fueraCarta->take(6);
                    $first = $tiles->shift();
                @endphp

                @if ($first)
                    <x-web-publica.tile
                        :contenido="$first"
                        cols="col-span-2 md:col-span-6 lg:col-span-8"
                        rows="row-span-2"
                        :sticker="['label' => 'Hoy', 'price' => $first->precioFormateado()]" />
                @endif

                @foreach ($tiles as $t)
                    <x-web-publica.tile
                        :contenido="$t"
                        cols="col-span-2 md:col-span-3 lg:col-span-4" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- 02 / Tap list preview --}}
    <section class="px-8 pb-24">
        <div class="mx-auto max-w-[1440px]">
            <x-web-publica.section-head
                num="02 / Tap list"
                eyebrow="Carta principal · cervezas"
                titulo="Siete tiradores. Rotan cuando hace falta."
                :accion="['label' => 'Ver carta completa', 'href' => route('web.carta')]" />

            <div class="mx-auto max-w-[960px]">
                @foreach ($destacados->take(4) as $i => $c)
                    <x-web-publica.tap-row :contenido="$c" :index="$i + 1" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- 03 / Stripes — experiencia --}}
    <section class="px-8 py-16">
        <div class="mx-auto max-w-[1440px]">
            <div class="grid grid-cols-1 border-t md:grid-cols-3" style="border-color: var(--v2-line);">
                <x-web-publica.stripe
                    num="01"
                    titulo="Cervezas vivas"
                    desc="La carta cambia cuando cambia la temporada. Si una referencia se queda sin stock, desaparece automaticamente. Si vuelve, vuelve."
                    icono="beer" />
                <x-web-publica.stripe
                    num="02"
                    titulo="Cocina para maridar"
                    desc="Tapas frias, elaboraciones calientes y fuera de carta pensados para acompanar la cerveza sin pelearse con ella."
                    icono="hops" />
                <x-web-publica.stripe
                    num="03"
                    titulo="Equipo del local"
                    desc="No somos sumilleres. Somos gente que lleva anos sirviendo cerveza y sabe lo que recomienda. Preguntanos."
                    icono="star" />
            </div>
        </div>
    </section>
</x-publico-layout>
