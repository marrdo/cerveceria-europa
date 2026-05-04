<?php

namespace App\Modulos\WebPublica\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaBlog extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'categorias_blog';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'activo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    /** @return BelongsToMany<PostBlog> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(PostBlog::class, 'categoria_blog_post', 'categoria_blog_id', 'post_blog_id');
    }
}
