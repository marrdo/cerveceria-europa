<?php

namespace App\Modulos\Compras\Models;

use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\UbicacionInventario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaDevolucionProveedor extends Model
{
    use HasUuids;

    protected $table = 'lineas_devolucion_proveedor';

    protected $fillable = [
        'devolucion_proveedor_id',
        'linea_pedido_compra_id',
        'producto_id',
        'ubicacion_inventario_id',
        'movimiento_inventario_id',
        'cantidad',
        'coste_unitario',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'coste_unitario' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<DevolucionProveedor, $this> */
    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionProveedor::class, 'devolucion_proveedor_id');
    }

    /** @return BelongsTo<LineaPedidoCompra, $this> */
    public function lineaPedido(): BelongsTo
    {
        return $this->belongsTo(LineaPedidoCompra::class, 'linea_pedido_compra_id');
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }

    /** @return BelongsTo<UbicacionInventario, $this> */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(UbicacionInventario::class, 'ubicacion_inventario_id')->withTrashed();
    }

    /** @return BelongsTo<MovimientoInventario, $this> */
    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }
}
