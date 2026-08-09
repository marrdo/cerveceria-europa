<?php

namespace Database\Seeders;

use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use Illuminate\Database\Seeder;

/**
 * Crea una identidad ficticia y segura para la instalación de demostración.
 */
class ConfiguracionNegocioSeeder extends Seeder
{
    public function run(): void
    {
        $configuracion = ConfiguracionNegocio::query()->firstOrCreate([
            'clave' => ConfiguracionNegocio::CLAVE_PRINCIPAL,
        ], [
            'nombre_comercial' => 'La Plaza Demo',
            'razon_social' => 'Hostelería La Plaza Demo, S.L.',
            'nif' => 'B00000000',
            'eslogan' => 'Sabores para compartir, momentos para volver',
            'descripcion_corta' => 'Bar de demostración con cocina cercana, bebidas y una carta preparada para probar el panel.',
            'telefono' => '600 000 000',
            'email' => 'hola@laplaza.demo',
            'direccion' => 'Calle Ejemplo, 1',
            'localidad' => 'Sevilla',
            'provincia' => 'Sevilla',
            'codigo_postal' => '41001',
            'pais' => 'España',
            'horario' => "Lunes a jueves: 08:00–23:00\nViernes a domingo: 08:00–00:00",
            'zona_horaria' => 'Europe/Madrid',
            'moneda' => 'EUR',
            'color_primario' => '#E3A13A',
            'color_secundario' => '#5D9B6E',
            'color_fondo' => '#0F0A06',
            'color_superficie' => '#1F1812',
            'color_texto' => '#F6ECD6',
            'seo_titulo' => 'La Plaza Demo · Bar y cocina en Sevilla',
            'seo_descripcion' => 'Carta, recomendaciones, horario y contacto de La Plaza Demo, un negocio ficticio preparado para probar el panel.',
            'seo_indexar' => false,
        ]);

        if ($configuracion->nombre_comercial === 'La Plaza Demo' && blank($configuracion->seo_titulo)) {
            $configuracion->update([
                'seo_titulo' => 'La Plaza Demo · Bar y cocina en Sevilla',
                'seo_descripcion' => 'Carta, recomendaciones, horario y contacto de La Plaza Demo, un negocio ficticio preparado para probar el panel.',
                'seo_indexar' => false,
            ]);
        }
    }
}
