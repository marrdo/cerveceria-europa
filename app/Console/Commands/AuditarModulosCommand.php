<?php

namespace App\Console\Commands;

use App\Modulos\Sistema\Modulos\AuditorModulos;
use Illuminate\Console\Command;

/**
 * Comprueba que la configuración comercial no pueda romper el panel.
 */
final class AuditarModulosCommand extends Command
{
    protected $signature = 'modulos:auditar';

    protected $description = 'Audita catálogo, estados y dependencias de los módulos';

    public function handle(AuditorModulos $auditor): int
    {
        $errores = $auditor->errores();

        if ($errores->isNotEmpty()) {
            $this->error('La configuración modular contiene incidencias:');
            $errores->each(fn (string $error) => $this->line(' - '.$error));

            return self::FAILURE;
        }

        $this->info('Configuración modular correcta: catálogo, estados y dependencias son coherentes.');

        return self::SUCCESS;
    }
}
