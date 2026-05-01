@props(['title', 'value', 'description' => null, 'variant' => 'default', 'icon' => null])

@php
    $card = [
        'default' => 'border-border bg-card',
        'warning' => 'border-warning/30 bg-warning/10',
        'success' => 'border-success/30 bg-success/10',
        'danger' => 'border-destructive/30 bg-destructive/10',
    ][$variant] ?? 'border-border bg-card';

    $iconClass = [
        'default' => 'bg-primary/10 text-primary',
        'warning' => 'bg-warning/20 text-warning-foreground',
        'success' => 'bg-success/20 text-success',
        'danger' => 'bg-destructive/20 text-destructive',
    ][$variant] ?? 'bg-primary/10 text-primary';
@endphp

<article {{ $attributes->merge(['class' => 'rounded-lg border p-4 '.$card]) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-1">
            <p class="text-sm font-medium text-muted-foreground">{{ $title }}</p>
            <p class="text-2xl font-bold text-foreground">{{ $value }}</p>
            @if ($description)
                <p class="text-xs text-muted-foreground">{{ $description }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $iconClass }}">
                {{ $icon }}
            </div>
        @endif
    </div>
</article>
