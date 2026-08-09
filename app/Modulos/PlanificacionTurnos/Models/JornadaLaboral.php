<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tramo continuo de trabajo de una persona.
 *
 * Un turno partido se representa mediante dos jornadas para la misma fecha.
 */
class JornadaLaboral extends Model
{
    use HasUuids;

    protected $table = 'jornadas_laborales';

    /** @var list<string> */
    protected $fillable = [
        'cuadrante_laboral_id',
        'usuario_id',
        'area_trabajo_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'termina_dia_siguiente',
        'minutos_descanso',
        'notas',
    ];

    /** @return BelongsTo<CuadranteLaboral, $this> */
    public function cuadrante(): BelongsTo
    {
        return $this->belongsTo(CuadranteLaboral::class, 'cuadrante_laboral_id');
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

    /**
     * Momento exacto de inicio del tramo.
     */
    public function inicio(): Carbon
    {
        return Carbon::parse($this->fecha->format('Y-m-d').' '.$this->hora_inicio);
    }

    /**
     * Momento exacto de finalizacion del tramo.
     */
    public function fin(): Carbon
    {
        $fin = Carbon::parse($this->fecha->format('Y-m-d').' '.$this->hora_fin);

        return $this->termina_dia_siguiente ? $fin->addDay() : $fin;
    }

    /**
     * Minutos efectivos, descontando la pausa no trabajada.
     */
    public function minutosEfectivos(): int
    {
        return max(0, (int) $this->inicio()->diffInMinutes($this->fin()) - $this->minutos_descanso);
    }

    /**
     * Horas efectivas con precision de dos decimales.
     */
    public function horasEfectivas(): float
    {
        return round($this->minutosEfectivos() / 60, 2);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'termina_dia_siguiente' => 'boolean',
            'minutos_descanso' => 'integer',
        ];
    }
}
