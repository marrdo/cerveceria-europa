@props([
    'num'   => '01',
    'titulo'=> '',
    'desc'  => '',
    'icono' => 'beer',  // 'beer' | 'hops' | 'star' | null
])

<article class="v2-stripe">
    <span class="font-mono text-xs font-medium tracking-wider text-amber-bright">{{ $num }}</span>
    <h3 class="my-6 font-display font-normal leading-none tracking-[0.005em] text-ink" style="font-size: clamp(1.6rem, 2.2vw, 2.2rem);">{{ $titulo }}</h3>
    <p class="m-0 max-w-[36ch] text-sm leading-[1.6] text-ink-mute">{{ $desc }}</p>

    <div class="v2-stripe-ico">
        @switch($icono)
            @case('beer')
                <x-brand.beer-icon class="h-full w-full" />
                @break
            @case('hops')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-full w-full">
                    <path d="M12 3c-3 1-4 4-3 7s4 4 3 7c-1-3-4-4-7-3 3-1 4-4 3-7s-4-4-3-7c1 3 4 4 7 3z"/>
                </svg>
                @break
            @case('star')
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-full w-full"><path d="M12 2l2.6 6.5L21 9l-5 4.4L17.5 21 12 17.6 6.5 21 8 13.4 3 9l6.4-.5z"/></svg>
                @break
        @endswitch
    </div>
</article>
