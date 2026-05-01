@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-md border border-success/25 bg-success/10 p-3 text-sm font-medium text-success']) }}>
        {{ $status }}
    </div>
@endif
