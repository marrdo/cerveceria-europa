<?php

namespace App\Modulos\Inventario\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadInventario extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'unidades_inventario';

    protected $fillable = ['nombre', 'codigo', 'permite_decimal', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return [
            'permite_decimal' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Productos que usan esta unidad para medir stock.
     *
     * @return HasMany<Producto>
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'unidad_inventario_id');
    }
}
