<?php

namespace App\Modulos\Inventario\Enums;

enum EstadoStockProducto: string
{
    case SinControl = 'sin_control';
    case SinStock = 'sin_stock';
    case Bajo = 'bajo';
    case Correcto = 'correcto';

    public function etiqueta(): string
    {
        return match ($this) {
            self::SinControl => 'Sin control',
            self::SinStock => 'Sin stock',
            self::Bajo => 'Stock bajo',
            self::Correcto => 'Correcto',
        };
    }
}
