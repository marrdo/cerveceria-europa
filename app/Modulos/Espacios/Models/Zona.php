<?php

namespace App\Modulos\Espacios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zona extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'zonas';

    protected $fillable = [
        'recinto_id',
        'nombre',
        'codigo',
        'orden',
        'notas',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'orden' => 'integer',
        ];
    }

    /** @return BelongsTo<Recinto, $this> */
    public function recinto(): BelongsTo
    {
        return $this->belongsTo(Recinto::class, 'recinto_id')->withTrashed();
    }

    /** @return HasMany<Mesa> */
    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'zona_id')->orderBy('orden')->orderBy('nombre');
    }
}
