<?php

namespace App\Support\Formato;

/**
 * Presenta cantidades de dominio sin ceros decimales innecesarios.
 *
 * La persistencia conserva hasta tres decimales porque inventario y compras
 * pueden trabajar con litros, kilos u otras unidades fraccionables. Esta clase
 * resuelve únicamente su representación para personas.
 */
final class FormateadorCantidad
{
    /**
     * Formatea una cantidad en notación española y elimina decimales de relleno.
     */
    public static function formatear(float|int|string|null $cantidad, int $maximoDecimales = 3): string
    {
        $decimales = max(0, min(6, $maximoDecimales));
        $formateada = number_format((float) ($cantidad ?? 0), $decimales, ',', '.');

        if ($decimales === 0) {
            return $formateada;
        }

        return rtrim(rtrim($formateada, '0'), ',');
    }
}
