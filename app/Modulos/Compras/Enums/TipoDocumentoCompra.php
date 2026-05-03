<?php

namespace App\Modulos\Compras\Enums;

enum TipoDocumentoCompra: string
{
    case Albaran = 'albaran';
    case Factura = 'factura';
    case Otro = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Albaran => 'Albaran',
            self::Factura => 'Factura',
            self::Otro => 'Otro documento',
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
