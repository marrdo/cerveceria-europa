@props(['size' => 'md'])

@php
    $buttonSize = [
        'sm' => 'h-9 w-9',
        'md' => 'h-10 w-10',
    ][$size] ?? 'h-10 w-10';

    $iconSize = [
        'sm' => 30,
        'md' => 34,
    ][$size] ?? 34;
@endphp

<button
    type="button"
    data-theme-toggle
    class="theme-toggle-button inline-flex {{ $buttonSize }} items-center justify-center"
    aria-label="Cambiar tema"
>
    <svg
        data-theme-toggle-icon
        class="theme-toggle-icon"
        viewBox="0 0 100 100"
        width="{{ $iconSize }}"
        height="{{ $iconSize }}"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <defs>
            <mask id="theme-toggle-moon-mask">
                <rect x="0" y="0" width="100%" height="100%" fill="white" />
                <circle cx="65" cy="35" r="22" fill="black" />
            </mask>
        </defs>

        <g>
            <g class="sun-rays">
                <line x1="50" y1="15" x2="50" y2="22" />
                <line x1="50" y1="78" x2="50" y2="85" />
                <line x1="15" y1="50" x2="22" y2="50" />
                <line x1="78" y1="50" x2="85" y2="50" />
                <line x1="25.2" y1="25.2" x2="30.1" y2="30.1" />
                <line x1="69.9" y1="69.9" x2="74.8" y2="74.8" />
                <line x1="25.2" y1="74.8" x2="30.1" y2="69.9" />
                <line x1="69.9" y1="30.1" x2="74.8" y2="25.2" />
            </g>

            <circle class="sun-core" cx="50" cy="50" r="22" />

            <g class="clouds">
                <ellipse cx="20" cy="70" rx="15" ry="10" />
                <ellipse cx="32" cy="75" rx="12" ry="8" />
                <ellipse cx="80" cy="65" rx="14" ry="9" />
            </g>
        </g>

        <g class="stars">
            <path d="M78 15l1.5 3 3 1.5-3 1.5-1.5 3-1.5-3-3-1.5 3-1.5z" />
            <path d="M90 30l2 4 4 2-4 2-2 4-2-4-4-2 4-2z" />
            <path d="M60 10l1 2 2 1-2 1-1 2-1-2-2-1 2-1z" />
            <path d="M15 25l2 4 4 2-4 2-2 4-2-4-4-2 4-2z" />
        </g>
    </svg>
    <span class="sr-only" data-theme-label>Tema: sistema</span>
</button>
