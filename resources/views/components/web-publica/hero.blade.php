@props([
    'eyebrow'  => 'Sevilla · 41002 · desde 1996',
    'titulo'   => null,    // string o HTML. Si es null, usa el predeterminado
    'lead'     => 'Bar con alma industrial en el centro de Sevilla. Seleccion rotatoria de cerveza de importacion y artesanas, cocina de bar pensada para maridar y una carta que cambia cuando cambia la temporada.',
    'stats'    => [
        ['n' => '120+', 'l' => 'Referencias en carta'],
        ['n' => '14',   'l' => 'Paises de origen'],
        ['n' => '7',    'l' => 'Tiradores en barra'],
    ],
    'imagen'   => null,    // url/path opcional; si null, usa la del CSS por defecto
])

<section class="v2-hero">
    <div class="v2-hero-bg" @if ($imagen) style="--v2-hero-image: url('{{ $imagen }}');" @endif></div>

    <div class="relative z-[2] mx-auto grid w-full max-w-[1440px] gap-10 px-8 py-16">
        <div class="flex items-center gap-4 text-[11px] font-bold uppercase tracking-[0.22em] text-amber-bright">
            <span class="h-px w-20 bg-amber-bright"></span>
            <span>{{ $eyebrow }}</span>
        </div>

        <h1 class="v2-hero-title">
            @if ($titulo)
                {!! $titulo !!}
            @else
                Cervezas<br>que cuentan<br><em>una historia</em>.
            @endif
        </h1>

        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-[1.2fr_1fr] lg:items-end lg:gap-16">
            <p class="m-0 max-w-[52ch] text-lg leading-[1.5] text-ink-mute lg:text-xl">{{ $lead }}</p>

            <div>
                <div class="grid grid-cols-3 gap-4 border-t pt-4" style="border-color: var(--v2-line-2);">
                    @foreach ($stats as $s)
                        <div>
                            <div class="font-mono text-3xl font-semibold tracking-tight text-amber-bright tabular-nums">{{ $s['n'] }}</div>
                            <div class="mt-1 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-ink-mute">{{ $s['l'] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('web.carta') }}" class="v2-btn v2-btn-primary">
                        Ver la carta
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('web.recomendaciones') }}" class="v2-btn v2-btn-ghost">Recomendaciones</a>
                </div>
            </div>
        </div>
    </div>
</section>
