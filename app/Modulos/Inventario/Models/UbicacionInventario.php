<?php

namespace App\Modulos\Inventario\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UbicacionInventario extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'ubicaciones_inventario';

    protected $fillable = ['nombre', 'codigo', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Registros de stock guardados en esta ubicacion.
     *
     * @return HasMany<StockInventario>
     */
    public function stock(): HasMany
    {
        return $this->hasMany(StockInventario::class, 'ubicacion_inventario_id');
    }
}
