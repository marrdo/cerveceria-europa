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
     * Estados que puede seleccionar una persona manualmente.
     *
     * Los estados de recepcion se calculan al registrar mercancia real.
     *
     * @return array<int, self>
     */
    public static function estadosCambioManual(): array
    {
        return [
            self::Pedido,
            self::Cerrado,
            self::Cancelado,
        ];
    }
}
