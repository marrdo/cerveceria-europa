@props(['title' => null, 'description' => null, 'titulo' => null, 'subtitulo' => null])

@php
    $resolvedTitle = $title ?? $titulo;
    $resolvedDescription = $description ?? $subtitulo;
@endphp

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        @if ($resolvedDescription)
            <p class="mt-1 text-sm text-muted-foreground">{{ $resolvedDescription }}</p>
        @endif
    </div>
    @if (isset($actions) || trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
