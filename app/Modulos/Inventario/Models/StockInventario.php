<?php

namespace App\Modulos\Inventario\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInventario extends Model
{
    use HasUuids;

    protected $table = 'stock_inventario';

    protected $fillable = ['producto_id', 'ubicacion_inventario_id', 'cantidad', 'cantidad_minima'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'cantidad_minima' => 'decimal:3',
        ];
    }

    /**
     * Producto al que pertenece este registro de stock.
     *
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Ubicacion donde se encuentra el stock.
     *
     * @return BelongsTo<UbicacionInventario, $this>
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(UbicacionInventario::class, 'ubicacion_inventario_id')->withTrashed();
    }
}
