<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tramo relativo a un día de la semana dentro de una plantilla.
 */
class PlantillaJornadaLaboral extends Model
{
    use HasUuids;

    protected $table = 'plantillas_jornadas_laborales';

    /** @var list<string> */
    protected $fillable = [
        'plantilla_id',
        'usuario_id',
        'area_trabajo_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'termina_dia_siguiente',
        'minutos_descanso',
        'notas',
    ];

    /** @return BelongsTo<PlantillaCuadranteLaboral, $this> */
    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaCuadranteLaboral::class, 'plantilla_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id')->withTrashed();
    }

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
            'termina_dia_siguiente' => 'boolean',
            'minutos_descanso' => 'integer',
        ];
    }
}
