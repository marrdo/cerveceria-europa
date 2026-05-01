<?php

namespace App\Enums;

enum RolUsuario: string
{
    case Camarero = 'camarero';
    case Encargado = 'encargado';
    case Propietario = 'propietario';
    case Superadmin = 'superadmin';
}
