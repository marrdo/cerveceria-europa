<?php

namespace App\Modulos\Configuracion\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        'zona_horaria',
        'moneda',
    ];

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
}
