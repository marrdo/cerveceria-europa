<?php

namespace Database\Seeders;

use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use Illuminate\Database\Seeder;

/**
 * Crea la identidad inicial de la instalacion sin pisar cambios posteriores.
 */
class ConfiguracionNegocioSeeder extends Seeder
{
    public function run(): void
    {
        ConfiguracionNegocio::query()->firstOrCreate(
            ['clave' => ConfiguracionNegocio::CLAVE_PRINCIPAL],
            ConfiguracionNegocio::valoresPorDefecto()->getAttributes(),
        );
    }
}
