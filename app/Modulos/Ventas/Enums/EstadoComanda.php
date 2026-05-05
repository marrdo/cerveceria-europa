<?php

namespace App\Modulos\Ventas\Enums;

enum EstadoComanda: string
{
    case Abierta = 'abierta';
    case EnPreparacion = 'en_preparacion';
    case Servida = 'servida';
    case Pagada = 'pagada';
    case Cancelada = 'cancelada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Abierta => 'Abierta',
            self::EnPreparacion => 'En preparacion',
            self::Servida => 'Servida',
            self::Pagada => 'Pagada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function variante(): string
    {
        return match ($this) {
            self::Abierta => 'info',
            self::EnPreparacion => 'warning',
            self::Servida => 'success',
            self::Pagada => 'success',
            self::Cancelada => 'danger',
        };
    }
}
