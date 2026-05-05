<?php

namespace App\Modulos\Espacios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recinto extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'recintos';

    protected $fillable = [
        'nombre_comercial',
        'nombre_fiscal',
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'pais',
        'telefono',
        'email',
        'notas',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** @return HasMany<Zona> */
    public function zonas(): HasMany
    {
        return $this->hasMany(Zona::class, 'recinto_id')->orderBy('orden')->orderBy('nombre');
    }
}
