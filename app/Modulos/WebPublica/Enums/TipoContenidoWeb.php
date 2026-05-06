<?php

namespace App\Modulos\WebPublica\Enums;

enum TipoContenidoWeb: string
{
    case Plato = 'plato';
    case Cerveza = 'cerveza';
    case Bebida = 'bebida';
    case RecomendacionChef = 'recomendacion_chef';
    case RecomendacionCerveza = 'recomendacion_cerveza';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Plato => 'Plato',
            self::Cerveza => 'Cerveza',
            self::Bebida => 'Bebida',
            self::RecomendacionChef => 'Recomendacion del chef',
            self::RecomendacionCerveza => 'Recomendacion de cerveza',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function valores(): array
    {
        return array_map(fn (self $tipo): string => $tipo->value, self::cases());
    }
}
