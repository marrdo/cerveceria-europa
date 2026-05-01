<?php

namespace App\Modulos\Compras\Models;

use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\UbicacionInventario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaRecepcionCompra extends Model
{
    use HasUuids;

    protected $table = 'lineas_recepcion_compra';

    protected $fillable = [
        'recepcion_compra_id',
        'linea_pedido_compra_id',
        'producto_id',
        'ubicacion_inventario_id',
        'movimiento_inventario_id',
        'cantidad',
        'coste_unitario',
        'codigo_lote',
        'caduca_el',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'coste_unitario' => 'decimal:2',
            'caduca_el' => 'date',
        ];
    }

    /** @return BelongsTo<RecepcionCompra, $this> */
    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionCompra::class, 'recepcion_compra_id');
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
