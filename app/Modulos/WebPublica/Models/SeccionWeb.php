<?php

namespace App\Modulos\WebPublica\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SeccionWeb extends Model
{
    use HasUuids;

    protected $table = 'secciones_web';

    protected $fillable = [
        'clave',
        'nombre',
        'titulo',
        'subtitulo',
        'contenido',
        'imagen_path',
        'datos',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'activo' => 'boolean',
        ];
    }

    /**
     * Devuelve una seccion por clave o una instancia vacia usable en vistas.
     */
    public static function porClave(string $clave): self
    {
        return self::query()->where('clave', $clave)->first()
            ?? new self([
                'clave' => $clave,
                'datos' => [],
                'activo' => true,
            ]);
    }

    public function urlImagen(): ?string
    {
        if (blank($this->imagen_path)) {
            return null;
        }

        if (str_starts_with($this->imagen_path, 'http://') || str_starts_with($this->imagen_path, 'https://')) {
            return $this->imagen_path;
        }

        return Storage::disk('public')->url($this->imagen_path);
    }
}
