<?php

namespace App\Modulos\Inventario\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'slug', 'cif_nif', 'email', 'telefono', 'persona_contacto', 'notas', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Productos que tienen este proveedor como proveedor principal.
     *
     * @return HasMany<Producto>
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
