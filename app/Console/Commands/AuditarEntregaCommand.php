<?php

namespace App\Console\Commands;

use App\Support\Entrega\AuditorEntrega;
use Illuminate\Console\Command;

/**
 * Presenta el control previo reproducible de una entrega o despliegue.
 */
final class AuditarEntregaCommand extends Command
{
    protected $signature = 'app:auditar-entrega';

    protected $description = 'Comprueba runtime, base de datos, almacenamiento y seguridad de producción';

    public function handle(AuditorEntrega $auditor): int
    {
        $resultados = $auditor->resultados();

        $this->table(
            ['Estado', 'Comprobación', 'Detalle'],
            $resultados->map(fn (array $resultado): array => [
                $resultado['nivel'],
                $resultado['comprobacion'],
                $resultado['detalle'],
            ])->all(),
        );

        if ($resultados->contains('nivel', 'ERROR')) {
            $this->error('La aplicación todavía no cumple todos los requisitos de entrega.');

            return self::FAILURE;
        }

        $this->info('Auditoría de entrega superada. Revisa los avisos antes de publicar.');

        return self::SUCCESS;
    }
}
