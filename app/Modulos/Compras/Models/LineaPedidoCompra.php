<?php

namespace App\Modulos\Compras\Models;

use App\Modulos\Inventario\Models\Producto;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaPedidoCompra extends Model
{
    use HasUuids;

    protected $table = 'lineas_pedido_compra';

    protected $fillable = [
        'pedido_compra_id',
        'producto_id',
        'descripcion',
        'cantidad',
        'coste_unitario',
        'iva_porcentaje',
        'subtotal',
        'impuestos',
        'total',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'coste_unitario' => 'decimal:2',
            'iva_porcentaje' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<PedidoCompra, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }
}
