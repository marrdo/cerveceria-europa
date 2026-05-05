<?php

namespace App\Modulos\Espacios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'mesas';

    protected $fillable = [
        'zona_id',
        'nombre',
        'capacidad',
        'orden',
        'notas',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'capacidad' => 'integer',
            'orden' => 'integer',
        ];
    }

    /** @return BelongsTo<Zona, $this> */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'zona_id')->withTrashed();
    }
}
