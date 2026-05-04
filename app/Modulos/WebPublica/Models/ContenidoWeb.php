<?php

namespace App\Modulos\WebPublica\Models;

use App\Modulos\Inventario\Models\Producto;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ContenidoWeb extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'contenidos_web';

    protected $fillable = [
        'tipo',
        'producto_id',
        'categoria_carta_id',
        'titulo',
        'slug',
        'descripcion_corta',
        'contenido',
        'precio',
        'alergenos',
        'imagen',
        'destacado',
        'fuera_carta',
        'publicado',
        'orden',
        'publicado_desde',
        'publicado_hasta',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoContenidoWeb::class,
            'precio' => 'decimal:2',
            'alergenos' => 'array',
            'destacado' => 'boolean',
            'fuera_carta' => 'boolean',
            'publicado' => 'boolean',
            'orden' => 'integer',
            'publicado_desde' => 'date',
            'publicado_hasta' => 'date',
        ];
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }

    /** @return BelongsTo<CategoriaCarta, $this> */
    public function categoriaCarta(): BelongsTo
    {
        return $this->belongsTo(CategoriaCarta::class, 'categoria_carta_id')->withTrashed();
    }

    /** @return HasMany<TarifaContenidoWeb, $this> */
    public function tarifas(): HasMany
    {
        return $this->hasMany(TarifaContenidoWeb::class, 'contenido_web_id')->orderBy('orden')->orderBy('nombre');
    }

    /**
     * Scope de contenido visible en la web publica.
     */
    public function scopePublicado($query)
    {
        return $query->where('publicado', true)
            ->where(function ($consulta): void {
                $consulta->whereNull('publicado_desde')->orWhereDate('publicado_desde', '<=', now()->toDateString());
            })
            ->where(function ($consulta): void {
                $consulta->whereNull('publicado_hasta')->orWhereDate('publicado_hasta', '>=', now()->toDateString());
            })
            ->where(function ($consulta): void {
                $consulta->whereNull('producto_id')
                    ->orWhereHas('producto', function ($productos): void {
                        $productos->where('activo', true)
                            ->where(function ($stock): void {
                                $stock->where('controla_stock', false)
                                    ->orWhereHas('stock', fn ($lineasStock) => $lineasStock->where('cantidad', '>', 0));
                            });
                    });
            });
    }

    /**
     * URL publica de la imagen o null si no tiene.
     */
    public function urlImagen(): ?string
    {
        if (blank($this->imagen)) {
            return null;
        }

        if (str_starts_with($this->imagen, 'http://') || str_starts_with($this->imagen, 'https://')) {
            return $this->imagen;
        }

        return Storage::disk('public')->url($this->imagen);
    }

    /**
     * Texto de precio listo para mostrar.
     */
    public function precioFormateado(): ?string
    {
        if ($this->precio === null) {
            return null;
        }

        return number_format((float) $this->precio, 2, ',', '.').' EUR';
    }

    /**
     * Indica si el contenido tiene tarifas especificas de carta.
     */
    public function tieneTarifas(): bool
    {
        return $this->relationLoaded('tarifas')
            ? $this->tarifas->isNotEmpty()
            : $this->tarifas()->exists();
    }
}
