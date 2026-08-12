<?php

namespace App\Modulos\Configuracion\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Identidad y datos operativos comunes de la instalacion.
 *
 * La aplicacion trabaja con un unico negocio por instalacion. Mantener estos
 * datos fuera de los modulos evita duplicarlos entre panel, web, caja e
 * informes, y permite reutilizar la base para otros locales.
 */
class ConfiguracionNegocio extends Model
{
    use HasUuids;

    public const CLAVE_PRINCIPAL = 'principal';

    protected $table = 'configuraciones_negocio';

    /** @var list<string> */
    protected $fillable = [
        'clave',
        'nombre_comercial',
        'razon_social',
        'nif',
        'eslogan',
        'descripcion_corta',
        'logo_path',
        'favicon_path',
        'imagen_social_path',
        'color_primario',
        'color_secundario',
        'color_fondo',
        'color_superficie',
        'color_texto',
        'telefono',
        'email',
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'pais',
        'horario',
        'web_url',
        'instagram_url',
        'google_maps_url',
        'reservas_url',
        'seo_titulo',
        'seo_descripcion',
        'seo_indexar',
        'zona_horaria',
        'moneda',
    ];

    protected function casts(): array
    {
        return [
            'seo_indexar' => 'boolean',
        ];
    }

    /**
     * Obtiene la configuracion activa o un objeto con valores seguros.
     *
     * El resultado se guarda solo durante la peticion actual para evitar
     * consultas repetidas al renderizar componentes Blade.
     */
    public static function actual(): self
    {
        $request = app()->bound('request') ? request() : null;
        $cacheKey = self::class.'.actual';

        if ($request?->attributes->has($cacheKey)) {
            return $request->attributes->get($cacheKey);
        }

        $configuracion = Schema::hasTable((new self)->getTable())
            ? self::query()->where('clave', self::CLAVE_PRINCIPAL)->first()
            : null;

        $configuracion ??= self::valoresPorDefecto();
        $request?->attributes->set($cacheKey, $configuracion);

        return $configuracion;
    }

    /**
     * Crea una instancia no persistida util durante instalacion y pruebas.
     */
    public static function valoresPorDefecto(): self
    {
        return new self([
            'clave' => self::CLAVE_PRINCIPAL,
            'nombre_comercial' => config('app.name', 'Mi negocio'),
            'pais' => 'Espana',
            'color_primario' => '#E3A13A',
            'color_secundario' => '#5D9B6E',
            'color_fondo' => '#0F0A06',
            'color_superficie' => '#1F1812',
            'color_texto' => '#F6ECD6',
            'seo_indexar' => false,
            'zona_horaria' => 'Europe/Madrid',
            'moneda' => 'EUR',
        ]);
    }

    /**
     * Compone la direccion postal sin mostrar separadores vacios.
     */
    public function direccionCompleta(): string
    {
        $localidad = trim(implode(' ', array_filter([
            $this->codigo_postal,
            $this->localidad,
        ])));

        return implode(', ', array_filter([
            $this->direccion,
            $localidad ?: null,
            $this->provincia,
        ]));
    }

    /** @return array<string, string> */
    public function variablesCssPublicas(): array
    {
        return [
            '--color-stout' => $this->hexARgb($this->color_fondo, '#0F0A06'),
            '--color-tile' => $this->hexARgb($this->color_superficie, '#1F1812'),
            '--color-ink' => $this->hexARgb($this->color_texto, '#F6ECD6'),
            '--color-ink-mute' => $this->hexARgb($this->mezclarHex($this->color_texto, $this->color_fondo, 65), '#AD9B7E'),
            '--color-amber-bright' => $this->hexARgb($this->color_primario, '#E3A13A'),
            '--color-amber-glow' => $this->hexARgb($this->mezclarHex($this->color_primario, '#FFFFFF', 30), '#F5C46B'),
            '--color-hops-bright' => $this->hexARgb($this->color_secundario, '#5D9B6E'),
            '--color-public-background' => $this->hexARgb($this->color_fondo, '#0F0A06'),
            '--color-public-foreground' => $this->hexARgb($this->color_texto, '#F6ECD6'),
            '--color-public-surface' => $this->hexARgb($this->color_superficie, '#1F1812'),
            '--color-public-primary' => $this->hexARgb($this->color_primario, '#E3A13A'),
            '--color-public-muted' => $this->hexARgb($this->mezclarHex($this->color_texto, $this->color_fondo, 65), '#AD9B7E'),
            '--color-public-border' => $this->hexARgb($this->mezclarHex($this->color_texto, $this->color_fondo, 82), '#51483C'),
        ];
    }

    public function urlRecurso(?string $ruta, ?string $alternativa = null): ?string
    {
        if (blank($ruta)) {
            return $alternativa;
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }

        return Storage::disk('public')->url($ruta);
    }

    public function urlCanonica(?string $ruta = null): string
    {
        if (blank($this->web_url)) {
            return url($ruta ?: request()->path());
        }

        $base = rtrim($this->web_url, '/');
        $ruta = trim((string) ($ruta ?? request()->path()), '/');

        return $ruta === '' ? $base : $base.'/'.$ruta;
    }

    private function hexARgb(?string $hex, string $alternativa): string
    {
        $hex = ltrim($hex ?: $alternativa, '#');

        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            $hex = ltrim($alternativa, '#');
        }

        return implode(' ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ]);
    }

    private function mezclarHex(?string $base, string $destino, int $porcentaje): string
    {
        $baseRgb = array_map('intval', explode(' ', $this->hexARgb($base, $destino)));
        $destinoRgb = array_map('intval', explode(' ', $this->hexARgb($destino, $destino)));
        $factor = max(0, min(100, $porcentaje)) / 100;
        $resultado = array_map(
            fn (int $valor, int $objetivo): int => (int) round($valor + (($objetivo - $valor) * $factor)),
            $baseRgb,
            $destinoRgb,
        );

        return sprintf('#%02X%02X%02X', ...$resultado);
    }
}
