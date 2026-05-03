<?php

namespace App\Modulos\Compras\Models;

use App\Modulos\Inventario\Models\Producto;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** @return HasMany<LineaRecepcionCompra> */
    public function recepciones(): HasMany
    {
        return $this->hasMany(LineaRecepcionCompra::class, 'linea_pedido_compra_id');
    }

    /** @return HasMany<LineaDevolucionProveedor> */
    public function devoluciones(): HasMany
    {
        return $this->hasMany(LineaDevolucionProveedor::class, 'linea_pedido_compra_id');
    }

    public function cantidadRecibida(): float
    {
        if ($this->relationLoaded('recepciones')) {
            return round((float) $this->recepciones->sum('cantidad'), 3);
        }

        return round((float) $this->recepciones()->sum('cantidad'), 3);
    }

    public function cantidadPendiente(): float
    {
        return max(0, round((float) $this->cantidad - $this->cantidadRecibida(), 3));
    }

    /**
     * Cantidad ya devuelta al proveedor para esta linea de pedido.
     */
    public function cantidadDevuelta(): float
    {
        if ($this->relationLoaded('devoluciones')) {
            return round((float) $this->devoluciones->sum('cantidad'), 3);
        }

        return round((float) $this->devoluciones()->sum('cantidad'), 3);
    }

    /**
     * Cantidad recibida que todavia puede devolverse.
     */
    public function cantidadDisponibleDevolucion(): float
    {
        return max(0, round($this->cantidadRecibida() - $this->cantidadDevuelta(), 3));
    }
}
