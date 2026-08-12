<?php

namespace App\Support\Entrega;

use App\Models\Usuario;
use App\Modulos\Sistema\Modulos\AuditorModulos;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Reúne comprobaciones reproducibles antes de entregar o desplegar la app.
 */
final readonly class AuditorEntrega
{
    public function __construct(
        private AuditorModulos $modulos,
        private Migrator $migrator,
    ) {}

    /**
     * @return Collection<int, array{nivel: string, comprobacion: string, detalle: string}>
     */
    public function resultados(): Collection
    {
        $resultados = collect();
        $this->comprobarRuntime($resultados);
        $this->comprobarBaseDatos($resultados);
        $this->comprobarAlmacenamiento($resultados);
        $this->comprobarProduccion($resultados);

        return $resultados;
    }

    /** @param Collection<int, array{nivel: string, comprobacion: string, detalle: string}> $resultados */
    private function comprobarRuntime(Collection $resultados): void
    {
        $this->agregar(
            $resultados,
            version_compare(PHP_VERSION, '8.4.0', '>='),
            'PHP 8.4+',
            PHP_VERSION,
        );
        $this->agregar($resultados, filled(config('app.key')), 'APP_KEY', 'Clave de cifrado configurada');
        $this->agregar(
            $resultados,
            is_file(public_path('build/manifest.json')),
            'Assets compilados',
            public_path('build/manifest.json'),
        );
        $this->agregar(
            $resultados,
            config('app.timezone') === 'Europe/Madrid',
            'Zona horaria',
            (string) config('app.timezone'),
        );
    }

    /** @param Collection<int, array{nivel: string, comprobacion: string, detalle: string}> $resultados */
    private function comprobarBaseDatos(Collection $resultados): void
    {
        try {
            DB::select('SELECT 1');
            $this->agregar($resultados, true, 'Conexión de base de datos', (string) config('database.default'));

            $conexion = (string) config('database.default');
            if (in_array($conexion, ['mysql', 'mariadb'], true)) {
                $configuracion = (array) config("database.connections.{$conexion}");
                $correcta = ($configuracion['charset'] ?? null) === 'utf8mb4'
                    && ($configuracion['engine'] ?? null) === 'InnoDB';
                $this->agregar($resultados, $correcta, 'MySQL UTF-8 e InnoDB', implode(' · ', [
                    (string) ($configuracion['charset'] ?? 'sin charset'),
                    (string) ($configuracion['collation'] ?? 'sin collation'),
                    (string) ($configuracion['engine'] ?? 'sin motor'),
                ]));
            }

            $archivos = $this->migrator->getMigrationFiles(database_path('migrations'));
            $ejecutadas = $this->migrator->repositoryExists()
                ? $this->migrator->getRepository()->getRan()
                : [];
            $pendientes = array_diff(array_keys($archivos), $ejecutadas);
            $this->agregar(
                $resultados,
                $pendientes === [],
                'Migraciones',
                $pendientes === [] ? 'Esquema actualizado' : count($pendientes).' pendientes',
            );

            $erroresModulos = $this->modulos->errores();
            $this->agregar(
                $resultados,
                $erroresModulos->isEmpty(),
                'Contratos modulares',
                $erroresModulos->isEmpty() ? 'Catálogo coherente' : $erroresModulos->implode(' | '),
            );
        } catch (Throwable $exception) {
            $this->agregar($resultados, false, 'Base de datos', $exception->getMessage());
        }
    }

    /** @param Collection<int, array{nivel: string, comprobacion: string, detalle: string}> $resultados */
    private function comprobarAlmacenamiento(Collection $resultados): void
    {
        $this->agregar($resultados, is_writable(storage_path()), 'Escritura en storage', storage_path());

        $enlacePublico = file_exists(public_path('storage'));
        $resultados->push([
            'nivel' => $enlacePublico ? 'OK' : 'AVISO',
            'comprobacion' => 'Enlace de archivos públicos',
            'detalle' => $enlacePublico ? public_path('storage') : 'Ejecuta php artisan storage:link',
        ]);
    }

    /** @param Collection<int, array{nivel: string, comprobacion: string, detalle: string}> $resultados */
    private function comprobarProduccion(Collection $resultados): void
    {
        if (! app()->environment('production')) {
            $resultados->push([
                'nivel' => 'AVISO',
                'comprobacion' => 'Entorno',
                'detalle' => 'Auditoría local; repítela con la configuración de producción',
            ]);

            return;
        }

        $this->agregar($resultados, ! config('app.debug'), 'APP_DEBUG', 'Debe ser false');
        $this->agregar($resultados, str_starts_with((string) config('app.url'), 'https://'), 'HTTPS', (string) config('app.url'));
        $this->agregar($resultados, ! config('demo.enabled'), 'Restauración demo', 'DEMO_MODE debe ser false');
        $this->agregar($resultados, config('session.secure') === true, 'Cookie de sesión segura', 'SESSION_SECURE_COOKIE=true');
        $this->agregar($resultados, config('session.encrypt') === true, 'Sesión cifrada', 'SESSION_ENCRYPT=true');
        $this->agregar($resultados, config('mail.default') !== 'log', 'Correo saliente', (string) config('mail.default'));
        $this->agregar($resultados, config('queue.default') !== 'sync', 'Cola persistente', (string) config('queue.default'));
        $this->agregar($resultados, config('cache.default') !== 'array', 'Caché persistente', (string) config('cache.default'));
        $this->agregar(
            $resultados,
            config('demo.superadmin.password') !== 'password',
            'Credencial inicial',
            'SUPERADMIN_PASSWORD no puede conservar el valor de demostración',
        );
        $cuentasDemo = Usuario::withTrashed()->where('email', 'like', '%@demo.local')->count();
        $this->agregar(
            $resultados,
            $cuentasDemo === 0,
            'Cuentas de demostración',
            $cuentasDemo === 0 ? 'No hay cuentas conocidas' : "Hay {$cuentasDemo} cuentas @demo.local",
        );
    }

    /** @param Collection<int, array{nivel: string, comprobacion: string, detalle: string}> $resultados */
    private function agregar(Collection $resultados, bool $correcto, string $comprobacion, string $detalle): void
    {
        $resultados->push([
            'nivel' => $correcto ? 'OK' : 'ERROR',
            'comprobacion' => $comprobacion,
            'detalle' => $detalle,
        ]);
    }
}
