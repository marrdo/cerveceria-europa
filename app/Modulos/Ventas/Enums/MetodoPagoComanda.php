<?php

namespace App\Modulos\Ventas\Enums;

enum MetodoPagoComanda: string
{
    case Efectivo = 'efectivo';
    case Tarjeta = 'tarjeta';
    case Bizum = 'bizum';
    case Invitacion = 'invitacion';
    case Otro = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Tarjeta => 'Tarjeta',
            self::Bizum => 'Bizum',
            self::Invitacion => 'Invitacion',
            self::Otro => 'Otro',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function valores(): array
    {
        return array_map(fn (self $metodo): string => $metodo->value, self::cases());
    }
}
