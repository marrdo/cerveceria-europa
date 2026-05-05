<?php

namespace App\Enums;

enum RolUsuario: string
{
    case Camarero = 'camarero';
    case Encargado = 'encargado';
    case Propietario = 'propietario';
    case Superadmin = 'superadmin';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Camarero => 'Camarero',
            self::Encargado => 'Encargado',
            self::Propietario => 'Propietario',
            self::Superadmin => 'Superadmin',
        };
    }
}
