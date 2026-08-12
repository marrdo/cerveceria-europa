<?php

namespace App\Modulos\WebPublica\ViewData;

use App\Modulos\Configuracion\Models\ConfiguracionNegocio;

/**
 * Construye los datos estructurados comunes de la web pública.
 */
class SeoPublicoViewData
{
    /** @return array<string, mixed> */
    public function construir(ConfiguracionNegocio $negocio): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $negocio->nombre_comercial,
            'description' => $negocio->seo_descripcion ?: $negocio->descripcion_corta,
            'url' => $negocio->urlCanonica('/'),
            'logo' => $negocio->urlRecurso($negocio->logo_path),
            'image' => $negocio->urlRecurso($negocio->imagen_social_path, $negocio->urlRecurso($negocio->logo_path)),
            'telephone' => $negocio->telefono,
            'email' => $negocio->email,
            'address' => $negocio->direccionCompleta() ? array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $negocio->direccion,
                'postalCode' => $negocio->codigo_postal,
                'addressLocality' => $negocio->localidad,
                'addressRegion' => $negocio->provincia,
                'addressCountry' => $negocio->pais,
            ]) : null,
            'sameAs' => array_values(array_filter([$negocio->instagram_url, $negocio->google_maps_url])),
        ], static fn (mixed $valor): bool => $valor !== null && $valor !== '' && $valor !== []);
    }
}
