@props(['title' => null, 'description' => null, 'titulo' => null, 'subtitulo' => null])

@php
    $resolvedTitle = $title ?? $titulo;
    $resolvedDescription = $description ?? $subtitulo;
@endphp

<div {{ $attributes->merge(['class' => 'mb-5 flex flex-col gap-4 border-b border-border pb-5 sm:mb-6 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        @if ($resolvedTitle)
            <h1 class="text-2xl font-extrabold tracking-[-0.025em] text-foreground sm:text-3xl">{{ $resolvedTitle }}</h1>
        @endif
        @if ($resolvedDescription)
            <p class="mt-1.5 max-w-3xl text-sm leading-6 text-muted-foreground">{{ $resolvedDescription }}</p>
        @endif
    </div>
    @if (isset($actions) || trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
