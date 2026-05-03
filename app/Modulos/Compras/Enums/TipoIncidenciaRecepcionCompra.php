<?php

namespace App\Modulos\Compras\Enums;

enum TipoIncidenciaRecepcionCompra: string
{
    case FaltaMercancia = 'falta_mercancia';
    case MenosCantidad = 'menos_cantidad';
    case ProductoEquivocado = 'producto_equivocado';
    case ProductoRoto = 'producto_roto';
    case MalEstado = 'mal_estado';
    case Otro = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::FaltaMercancia => 'Falta mercancia',
            self::MenosCantidad => 'Llega menos cantidad',
            self::ProductoEquivocado => 'Producto equivocado',
            self::ProductoRoto => 'Producto roto',
            self::MalEstado => 'Producto en mal estado',
            self::Otro => 'Otro motivo',
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
