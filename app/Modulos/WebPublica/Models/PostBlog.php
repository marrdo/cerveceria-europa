<?php

namespace App\Modulos\WebPublica\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PostBlog extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'posts_blog';

    protected $fillable = [
        'titulo',
        'slug',
        'resumen',
        'contenido',
        'imagen',
        'autor',
        'publicado',
        'destacado',
        'publicado_at',
    ];

    protected function casts(): array
    {
        return [
            'publicado' => 'boolean',
            'destacado' => 'boolean',
            'publicado_at' => 'datetime',
        ];
    }

    /**
     * Scope de posts visibles en la web publica.
     */
    public function scopePublicado($query)
    {
        return $query->where('publicado', true)
            ->where(function ($consulta): void {
                $consulta->whereNull('publicado_at')->orWhere('publicado_at', '<=', now());
            });
    }

    /**
     * URL publica de la imagen.
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

    /** @return BelongsToMany<CategoriaBlog> */
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(CategoriaBlog::class, 'categoria_blog_post', 'post_blog_id', 'categoria_blog_id');
    }
}
