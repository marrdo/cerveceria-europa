<?php

namespace App\Modulos\WebPublica\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaCarta extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'categorias_carta';

    protected $fillable = [
        'categoria_padre_id',
        'nombre',
        'slug',
        'descripcion',
        'activo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    /** @return BelongsTo<CategoriaCarta, $this> */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'categoria_padre_id');
    }

    /** @return HasMany<CategoriaCarta, $this> */
    public function hijas(): HasMany
    {
        return $this->hasMany(self::class, 'categoria_padre_id')->orderBy('orden')->orderBy('nombre');
    }

    /** @return HasMany<ContenidoWeb, $this> */
    public function contenidos(): HasMany
    {
        return $this->hasMany(ContenidoWeb::class, 'categoria_carta_id')->orderBy('orden')->orderBy('titulo');
    }

    /**
     * Nombre indentado para selects del panel.
     */
    public function nombreJerarquico(): string
    {
        return $this->padre ? $this->padre->nombre.' / '.$this->nombre : $this->nombre;
    }
}
