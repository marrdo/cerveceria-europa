<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Enums\TipoIncidenciaLaboral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Periodo que modifica la disponibilidad de una persona o del negocio.
 *
 * Las incidencias no pertenecen a un cuadrante porque unas vacaciones o una
 * baja pueden atravesar varias semanas naturales.
 */
class IncidenciaLaboral extends Model
{
    use HasUuids;

    protected $table = 'incidencias_laborales';

    /** @var list<string> */
    protected $fillable = [
        'usuario_id',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'notas',
        'creado_por_id',
    ];

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id')->withTrashed();
    }

    /** @return BelongsTo<Usuario, $this> */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id')->withTrashed();
    }

    public function afectaFecha(Carbon $fecha): bool
    {
        return $fecha->betweenIncluded($this->fecha_inicio, $this->fecha_fin);
    }

    public function esGlobal(): bool
    {
        return $this->tipo->esGlobal();
    }

    /**
     * Restringe incidencias que tocan, al menos, un día del periodo indicado.
     *
     * @param  Builder<IncidenciaLaboral>  $query
     * @return Builder<IncidenciaLaboral>
     */
    public function scopeCoincideConPeriodo(Builder $query, Carbon $inicio, Carbon $fin): Builder
    {
        return $query
            ->whereDate('fecha_inicio', '<=', $fin->toDateString())
            ->whereDate('fecha_fin', '>=', $inicio->toDateString());
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tipo' => TipoIncidenciaLaboral::class,
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }
}
