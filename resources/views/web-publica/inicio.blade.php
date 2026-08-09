<x-publico-layout>
    <x-web-publica.hero :negocio="$negocio" />

    <x-web-publica.ticker :items="$fueraCarta" />

    {{-- Sugerencias editables de la demo. --}}
    <section class="px-8 py-24">
        <div class="mx-auto max-w-[1440px]">
            <x-web-publica.section-head
                num="01 / Sugerencias"
                eyebrow="Fuera de carta · disponibilidad limitada"
                titulo="Lo que el equipo <em class='not-italic text-amber-bright'>recomienda hoy</em>."
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

    {{-- Selección destacada de la carta. --}}
    <section class="px-8 pb-24">
        <div class="mx-auto max-w-[1440px]">
            <x-web-publica.section-head
                num="02 / Favoritos"
                eyebrow="Carta principal · selección demo"
                titulo="Una carta sencilla, clara y totalmente editable."
                :accion="['label' => 'Ver carta completa', 'href' => route('web.carta')]" />

            <div class="mx-auto max-w-[960px]">
                @foreach ($destacados->take(4) as $i => $c)
                    <x-web-publica.tap-row :contenido="$c" :index="$i + 1" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Valores de una demo de hostelería neutral. --}}
    <section class="px-8 py-16">
        <div class="mx-auto max-w-[1440px]">
            <div class="grid grid-cols-1 border-t md:grid-cols-3" style="border-color: var(--v2-line);">
                <x-web-publica.stripe
                    num="01"
                    titulo="Carta conectada"
                    desc="Los productos publicados pueden vincularse al inventario y ocultarse automáticamente cuando se agota el stock."
                    icono="servicio" />
                <x-web-publica.stripe
                    num="02"
                    titulo="Operativa ordenada"
                    desc="Carta, ventas, compras, inventario, personal y turnos comparten datos sin duplicar trabajo."
                    icono="hops" />
                <x-web-publica.stripe
                    num="03"
                    titulo="Identidad configurable"
                    desc="El nombre, los datos de contacto y los textos visibles salen de la configuración del negocio."
                    icono="star" />
            </div>
        </div>
    </section>
</x-publico-layout>
