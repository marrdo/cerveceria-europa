<x-publico-layout :title="$titulo . ' | Cerveceria Europa'" :description="$descripcion">
    {{-- Page header rompedor --}}
    <section class="relative overflow-hidden border-b px-8 pb-14 pt-20" style="border-color: var(--v2-line);">
        <div class="mx-auto max-w-[1440px]">
            <div class="font-mono text-[13px] font-medium tracking-wider text-amber-bright">/ {{ $eyebrowSeccion ?? 'Carta · seccion' }}</div>
            <h1 class="relative z-[2] m-0 font-display leading-[0.82] tracking-[-0.01em] text-ink" style="font-size: clamp(5rem, 18vw, 22rem);">{{ $titulo }}.</h1>
            <p class="relative z-[2] mt-8 max-w-[56ch] text-lg leading-[1.5] text-ink-mute">{{ $descripcion }}</p>
        </div>
    </section>

    {{-- Grid 3-col de list-cards --}}
    <section class="px-8 py-24">
        <div class="mx-auto max-w-[1440px]">
            @if ($contenidos->isNotEmpty())
                <ul class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($contenidos as $c)
                        <li>
                            <x-web-publica.list-card :contenido="$c" />
                        </li>
                    @endforeach
                </ul>
                <div class="mt-12">
                    {{ $contenidos->links() }}
                </div>
            @else
                <div class="rounded-3xl border border-dashed p-20 text-center text-base text-ink-mute" style="border-color: var(--v2-line-2);">
                    Todavia no hay contenido publicado en esta seccion.
                </div>
            @endif
        </div>
    </section>
</x-publico-layout>
