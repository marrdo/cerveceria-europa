<x-publico-layout title="Contacto" :description="$seccion->contenido">
    @php
        $datos = $seccion->datos ?? [];
        $imagen = $seccion->urlImagen();
    @endphp

    <section class="relative overflow-hidden border-b px-8 pb-14 pt-20" style="border-color: var(--v2-line);">
        <div class="mx-auto max-w-[1440px]">
            <div class="font-mono text-[13px] font-medium tracking-wider text-amber-bright">/ Visítanos</div>
            <h1 class="relative z-[2] m-0 font-display leading-[0.82] tracking-[-0.01em] text-ink" style="font-size: clamp(5rem, 18vw, 22rem);">Contacto.</h1>
        </div>
    </section>

    <section class="px-8 py-24" aria-labelledby="contacto-heading">
        <div class="mx-auto max-w-[1440px]">
            <div class="grid grid-cols-1 items-start gap-10 lg:grid-cols-[1.3fr_1fr] lg:gap-20">
                <article>
                    <h2 id="contacto-heading" class="m-0 mb-6 font-display leading-[0.9] tracking-[0.005em] text-ink" style="font-size: clamp(3rem, 6vw, 5.5rem);">
                        {{ $seccion->titulo ?: 'Ven a conocernos' }}@if($seccion->subtitulo)<br>{{ $seccion->subtitulo }}@endif
                    </h2>
                    <p class="mb-10 text-lg leading-[1.55] text-ink-mute">{{ $seccion->contenido ?: $negocio->descripcion_corta }}</p>

                    <dl class="m-0 flex flex-col">
                        @foreach (([
                            ['Dirección', $negocio->direccionCompleta()],
                            ['Teléfono', $negocio->telefono],
                            ['Email', $negocio->email],
                            ['Reservas', $datos['reservas'] ?? 'Por teléfono o email'],
                            ['Horario', $negocio->horario],
                        ]) as [$etiqueta, $valor])
                            @continue(blank($valor))
                            <div class="grid grid-cols-[140px_1fr] items-baseline gap-5 border-t py-5" style="border-color: var(--v2-line);">
                                <dt class="text-[11px] font-bold uppercase tracking-[0.16em] text-amber-bright">{{ $etiqueta }}</dt>
                                <dd class="m-0 whitespace-pre-line text-base leading-[1.5] text-ink">{{ $valor }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>

                <figure class="v2-contacto-photo" @if($imagen) style="--v2-contact-image: url('{{ $imagen }}');" @endif aria-label="{{ $negocio->nombre_comercial }}">
                    <div class="absolute bottom-6 left-6 flex flex-col leading-none">
                        @if ($negocio->codigo_postal)<span class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-amber-bright">{{ $negocio->codigo_postal }} · {{ $negocio->provincia }}</span>@endif
                        <span class="mt-2 font-display text-3xl tracking-[0.005em] text-ink">{{ $negocio->localidad ?: $negocio->nombre_comercial }}</span>
                    </div>
                </figure>
            </div>
        </div>
    </section>
</x-publico-layout>
