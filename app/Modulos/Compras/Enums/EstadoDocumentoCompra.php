<?php

namespace App\Modulos\Compras\Enums;

enum EstadoDocumentoCompra: string
{
    case Pendiente = 'pendiente';
    case EnRevision = 'en_revision';
    case Procesado = 'procesado';
    case Descartado = 'descartado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnRevision => 'En revision',
            self::Procesado => 'Procesado',
            self::Descartado => 'Descartado',
        };
    }

    public function variante(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::EnRevision => 'info',
            self::Procesado => 'success',
            self::Descartado => 'danger',
        };
    }
}
