@props([
    'num'     => null,   // '01 / Hoy en barra'
    'eyebrow' => null,   // 'Fuera de carta · rotacion diaria'
    'titulo'  => '',     // string o HTML
    'accion'  => null,   // ['label' => 'Ver todo', 'href' => route(...)]
])

<header class="mb-14 grid grid-cols-1 items-end gap-6 border-b pb-7 lg:grid-cols-[auto_1fr_auto]" style="border-color: var(--v2-line);">
    <div>
        @if ($num)
            <div class="font-mono text-[13px] font-medium tracking-wider text-amber-bright">{{ $num }}</div>
        @endif
        @if ($eyebrow)
            <p class="mt-1.5 text-[11px] font-bold uppercase tracking-[0.24em] text-ink-mute">{{ $eyebrow }}</p>
        @endif
    </div>
    <h2 class="m-0 font-display font-normal leading-[0.95] tracking-[0.005em] text-ink" style="font-size: clamp(2.6rem, 6vw, 5rem); text-wrap: balance;">{!! $titulo !!}</h2>
    @if ($accion)
        <a href="{{ $accion['href'] }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.08em] text-amber-bright hover:text-ink transition-colors">
            {{ $accion['label'] }}
            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
    @endif
</header>
