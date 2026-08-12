<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Planificación laboral correspondiente a una semana natural.
 */
class CuadranteLaboral extends Model
{
    use HasUuids;

    protected $table = 'cuadrantes_laborales';

    /** @var list<string> */
    protected $fillable = [
        'semana_inicio',
        'estado',
        'notas',
        'publicado_at',
        'publicado_por_id',
    ];

    /**
     * Jornadas incluidas en la semana.
     *
     * @return HasMany<JornadaLaboral, $this>
     */
    public function jornadas(): HasMany
    {
        return $this->hasMany(JornadaLaboral::class, 'cuadrante_laboral_id');
    }

    /**
     * Versiones Excel creadas en cada publicación del cuadrante.
     *
     * @return HasMany<ExportacionCuadranteLaboral, $this>
     */
    public function exportaciones(): HasMany
    {
        return $this->hasMany(ExportacionCuadranteLaboral::class, 'cuadrante_laboral_id');
    }

    /**
     * Usuario que publico la version visible del cuadrante.
     *
     * @return BelongsTo<Usuario, $this>
     */
    public function publicadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'publicado_por_id');
    }

    /**
     * Último día incluido en la semana.
     */
    public function semanaFin(): Carbon
    {
        return $this->semana_inicio->copy()->addDays(6);
    }

    /**
     * Indica si el cuadrante sigue admitiendo cambios operativos.
     */
    public function esBorrador(): bool
    {
        return $this->estado === EstadoCuadranteLaboral::Borrador;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'semana_inicio' => 'date',
            'estado' => EstadoCuadranteLaboral::class,
            'publicado_at' => 'datetime',
        ];
    }
}
