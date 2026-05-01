<?php

namespace App\Modulos\Inventario\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoteInventario extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'lotes_inventario';

    protected $fillable = [
        'producto_id',
        'ubicacion_inventario_id',
        'proveedor_id',
        'movimiento_entrada_id',
        'codigo_lote',
        'cantidad_inicial',
        'cantidad_disponible',
        'recibido_el',
        'caduca_el',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_inicial' => 'decimal:3',
            'cantidad_disponible' => 'decimal:3',
            'recibido_el' => 'date',
            'caduca_el' => 'date',
            'activo' => 'boolean',
        ];
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /** @return BelongsTo<UbicacionInventario, $this> */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(UbicacionInventario::class, 'ubicacion_inventario_id')->withTrashed();
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withTrashed();
    }

    /** @return BelongsTo<MovimientoInventario, $this> */
    public function movimientoEntrada(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_entrada_id');
    }
}
