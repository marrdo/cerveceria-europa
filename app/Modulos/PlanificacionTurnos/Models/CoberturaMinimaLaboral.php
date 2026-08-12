<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Número mínimo de personas requeridas en un área y franja recurrente.
 */
class CoberturaMinimaLaboral extends Model
{
    use HasUuids;

    protected $table = 'coberturas_minimas_laborales';

    /** @var list<string> */
    protected $fillable = [
        'area_trabajo_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'minimo_personas',
        'activo',
    ];

    /** @return BelongsTo<AreaTrabajo, $this> */
    public function areaTrabajo(): BelongsTo
    {
        return $this->belongsTo(AreaTrabajo::class, 'area_trabajo_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'minimo_personas' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
