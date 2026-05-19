<x-publico-layout title="Contacto | Cerveceria Europa">
    @php($datos = $seccion->datos ?? [])

    {{-- Page header --}}
    <section class="relative overflow-hidden border-b px-8 pb-14 pt-20" style="border-color: var(--v2-line);">
        <div class="mx-auto max-w-[1440px]">
            <div class="font-mono text-[13px] font-medium tracking-wider text-amber-bright">/ Ven a la barra</div>
            <h1 class="relative z-[2] m-0 font-display leading-[0.82] tracking-[-0.01em] text-ink" style="font-size: clamp(5rem, 18vw, 22rem);">Contacto.</h1>
        </div>
    </section>

    <section class="px-8 py-24" aria-labelledby="contacto-heading">
        <div class="mx-auto max-w-[1440px]">
            <div class="grid grid-cols-1 items-start gap-10 lg:grid-cols-[1.3fr_1fr] lg:gap-20">
                <article>
                    <h2 id="contacto-heading" class="m-0 mb-6 font-display leading-[0.9] tracking-[0.005em] text-ink" style="font-size: clamp(3rem, 6vw, 5.5rem);">
                        {{ $seccion->titulo ?: 'Sevilla.' }}<br>{{ $seccion->subtitulo ?: 'Centro historico.' }}
                    </h2>
                    <p class="mb-10 text-lg leading-[1.55] text-ink-mute">
                        {{ $seccion->contenido ?: 'Mejor reserva si venis mas de cuatro. La cocina para a las 23:30 entre semana, a las 01:00 los fines.' }}
                    </p>

                    <dl class="m-0 flex flex-col">
                        @foreach (([
                            ['Direccion', $datos['ubicacion'] ?? 'Calle Trajano · 41002 Sevilla'],
                            ['Telefono',  $datos['telefono']  ?? '+34 955 00 00 00'],
                            ['Email',     $datos['email']     ?? 'hola@cerveceriaeuropa.es'],
                            ['Reservas',  $datos['reservas']  ?? 'Por telefono o email'],
                            ['Horario',   $datos['horario']   ?? 'Mar–Jue 12:00–24:00 · Vie–Sab 12:00–01:30 · Dom 12:00–17:00'],
                        ]) as [$k, $v])
                            <div class="grid grid-cols-[140px_1fr] items-baseline gap-5 border-t py-5" style="border-color: var(--v2-line);">
                                <dt class="text-[11px] font-bold uppercase tracking-[0.16em] text-amber-bright">{{ $k }}</dt>
                                <dd class="m-0 text-base leading-[1.5] text-ink">{{ $v }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>

                <figure class="v2-contacto-photo" aria-label="Interior de Cerveceria Europa">
                    <div class="absolute bottom-6 left-6 flex flex-col leading-none">
                        <span class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-amber-bright">Plot · 41002</span>
                        <span class="mt-2 font-display text-3xl tracking-[0.005em] text-ink">Calle Trajano</span>
                    </div>
                </figure>
            </div>
        </div>
    </section>
</x-publico-layout>
