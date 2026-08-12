<?php

namespace App\Console\Commands;

use App\Support\Demo\RestauradorDemo;
use Illuminate\Console\Command;
use Throwable;

/**
 * Punto de entrada protegido para reconstruir la base de demostración.
 */
final class RestaurarDemoCommand extends Command
{
    protected $signature = 'demo:restaurar {--force : Omite la confirmación interactiva}';

    protected $description = 'Elimina y reconstruye exclusivamente la base de datos configurada como demo';

    public function handle(RestauradorDemo $restaurador): int
    {
        if (app()->environment('production')) {
            $this->error('La restauración de la demo está bloqueada en producción.');

            return self::FAILURE;
        }

        if (! config('demo.enabled')) {
            $this->error('DEMO_MODE no está activado. No se ha modificado ningún dato.');

            return self::FAILURE;
        }

        $conexion = (string) config('database.default');
        $baseReal = (string) config("database.connections.{$conexion}.database");
        $basePermitida = (string) config('demo.database');

        if ($basePermitida === '' || ! hash_equals($basePermitida, $baseReal)) {
            $this->error("La base conectada [{$baseReal}] no coincide con DEMO_DATABASE. Operación cancelada.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Se eliminarán todos los datos y archivos de [{$baseReal}]. ¿Continuar?")) {
            $this->warn('Restauración cancelada.');

            return self::SUCCESS;
        }

        try {
            $codigo = $restaurador->ejecutar();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('La restauración no pudo completarse: '.$exception->getMessage());

            return self::FAILURE;
        }

        if ($codigo !== self::SUCCESS) {
            $this->error('La reconstrucción de la base devolvió un error.');

            return self::FAILURE;
        }

        $this->info('Demo restaurada: migraciones, seeders y almacenamiento vuelven al estado inicial.');

        return self::SUCCESS;
    }
}
