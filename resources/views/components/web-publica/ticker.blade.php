@props([
    'items' => [], // collection o array de ContenidoWeb (titulo + precioFormateado())
])

@php
    // Duplicamos los items para que el loop CSS sea continuo
    $loop = collect($items)->concat(collect($items))->all();
@endphp

@if (count($loop) > 0)
    <div class="v2-ticker" aria-label="Fuera de carta hoy">
        <div class="v2-ticker-track">
            @foreach ($loop as $it)
                <span class="v2-ticker-item">
                    <svg class="h-4 w-4 flex-none" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.6 6.5L21 9l-5 4.4L17.5 21 12 17.6 6.5 21 8 13.4 3 9l6.4-.5z"/></svg>
                    <span>{{ is_object($it) ? $it->titulo : ($it['name'] ?? '') }}</span>
                    <span class="b">{{ is_object($it) ? $it->precioFormateado() : ($it['price'] ?? '') }}</span>
                </span>
            @endforeach
        </div>
    </div>
@endif
