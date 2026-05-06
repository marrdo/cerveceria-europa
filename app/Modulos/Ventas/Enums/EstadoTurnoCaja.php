<?php

namespace App\Modulos\Ventas\Enums;

enum EstadoTurnoCaja: string
{
    case Abierta = 'abierta';
    case Cerrada = 'cerrada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Abierta => 'Abierta',
            self::Cerrada => 'Cerrada',
        };
    }

    public function variante(): string
    {
        return match ($this) {
            self::Abierta => 'success',
            self::Cerrada => 'default',
        };
    }
}
