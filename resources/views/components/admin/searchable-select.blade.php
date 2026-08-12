@props([
    'id',
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Selecciona...',
    'searchPlaceholder' => 'Buscar por nombre...',
])

@php
    $normalizar = static fn (string $texto): string => mb_strtolower(
        \Illuminate\Support\Str::ascii($texto),
    );
@endphp

<div
    x-data="{
        busqueda: '',
        coincide(etiqueta) {
            const texto = etiqueta.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            const termino = this.busqueda.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();

            return termino === '' || texto.includes(termino);
        },
    }"
    class="space-y-1.5"
>
    <div class="relative">
        <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
        </svg>
        <input
            type="search"
            x-model.debounce.150ms="busqueda"
            class="admin-input block h-11 w-full ps-9"
            placeholder="{{ $searchPlaceholder }}"
            aria-label="{{ $searchPlaceholder }}"
            autocomplete="off"
        >
    </div>

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'admin-input block h-11 w-full']) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $value => $label)
            <option
                value="{{ $value }}"
                @selected((string) $selected === (string) $value)
                x-bind:hidden="! coincide(@js($normalizar((string) $label)))"
            >{{ $label }}</option>
        @endforeach
    </select>

    <p x-show="busqueda !== ''" class="text-[11px] text-muted-foreground">
        La lista muestra únicamente las coincidencias.
    </p>
</div>
