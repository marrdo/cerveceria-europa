@props([
    'horario' => null, // ['apertura' => '12:00', 'cierre' => '24:00'] o null para auto-leer de Seccion 'contacto'
])

@php
    // Logica minima: si esta entre apertura y cierre del dia, "abierto".
    // Si quieres logica por dia de la semana, sustituye por un helper App\Support\Horario::abierto().
    $now = now();
    $apertura = $horario['apertura'] ?? '12:00';
    $cierre   = $horario['cierre']   ?? '00:00';

    $hoy = $now->format('H:i');
    $abierto = $cierre === '00:00'
        ? $hoy >= $apertura
        : ($hoy >= $apertura && $hoy <= $cierre);

    $cierreVisible = $cierre === '00:00' ? '00:00' : $cierre;
@endphp

<div class="v2-live" aria-live="polite">
    <span class="v2-live-dot"></span>
    <span>{{ $abierto ? 'En barra · hasta ' . $cierreVisible : 'Cerrado · vuelve manana' }}</span>
</div>
