<?php

namespace App\Modulos\Ventas\Enums;

enum EstadoLineaComanda: string
{
    case Pendiente = 'pendiente';
    case EnPreparacion = 'en_preparacion';
    case Servida = 'servida';
    case Cancelada = 'cancelada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnPreparacion => 'En preparacion',
            self::Servida => 'Servida',
            self::Cancelada => 'Cancelada',
        };
    }

    public function variante(): string
    {
        return match ($this) {
            self::Pendiente => 'default',
            self::EnPreparacion => 'warning',
            self::Servida => 'success',
            self::Cancelada => 'danger',
        };
    }
}
