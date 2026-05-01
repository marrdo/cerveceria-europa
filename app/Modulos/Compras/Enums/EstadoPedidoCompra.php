<?php

namespace App\Modulos\Compras\Enums;

enum EstadoPedidoCompra: string
{
    case Borrador = 'borrador';
    case Pedido = 'pedido';
    case RecibidoParcial = 'recibido_parcial';
    case Recibido = 'recibido';
    case Cerrado = 'cerrado';
    case Cancelado = 'cancelado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Pedido => 'Pedido',
            self::RecibidoParcial => 'Recibido parcial',
            self::Recibido => 'Recibido',
            self::Cerrado => 'Cerrado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function variante(): string
    {
        return match ($this) {
            self::Borrador => 'default',
            self::Pedido => 'info',
            self::RecibidoParcial => 'warning',
            self::Recibido => 'success',
            self::Cerrado => 'success',
            self::Cancelado => 'danger',
        };
    }

    /**
     * Estados que se pueden seleccionar manualmente desde la fase 2.0.
     *
     * @return array<int, self>
     */
    public static function estadosOperativos(): array
    {
        return [
            self::Pedido,
            self::RecibidoParcial,
            self::Recibido,
            self::Cerrado,
            self::Cancelado,
        ];
    }
}
