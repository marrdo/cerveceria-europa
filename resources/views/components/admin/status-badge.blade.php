@props(['variant' => 'default'])

@php
    $classes = [
        'default' => 'border-border bg-secondary text-secondary-foreground',
        'success' => 'border-success/30 bg-success/15 text-success',
        'warning' => 'border-warning/30 bg-warning/15 text-warning',
        'danger' => 'border-destructive/30 bg-destructive/15 text-destructive',
        'info' => 'border-primary/30 bg-primary/10 text-primary',
    ][$variant] ?? 'border-border bg-secondary text-secondary-foreground';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium '.$classes]) }}>
    {{ $slot }}
</span>
