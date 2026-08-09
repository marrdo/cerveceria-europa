<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Área operativa en la que puede trabajar una persona.
 *
 * Ejemplos: sala/barra, cocina o trastienda. No se reutilizan las zonas del
 * módulo Espacios porque estas áreas pertenecen a la organización laboral.
 */
class AreaTrabajo extends Model
{
    use HasUuids;

    protected $table = 'areas_trabajo';

    /** @var list<string> */
    protected $fillable = [
        'nombre',
        'color',
        'activo',
        'orden',
    ];

    /**
     * Jornadas asignadas al área.
     *
     * @return HasMany<JornadaLaboral, $this>
     */
    public function jornadas(): HasMany
    {
        return $this->hasMany(JornadaLaboral::class, 'area_trabajo_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }
}
