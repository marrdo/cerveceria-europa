<?php

namespace App\Support\Demo;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

/**
 * Reconstruye una instalación demo y elimina los archivos vinculados a la
 * base anterior para no dejar documentos o imágenes huérfanos.
 */
class RestauradorDemo
{
    public function ejecutar(): int
    {
        $this->limpiarAlmacenamiento();
        Artisan::call('optimize:clear');

        return Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);
    }

    private function limpiarAlmacenamiento(): void
    {
        foreach ((array) config('demo.storage.public', []) as $directorio) {
            Storage::disk('public')->deleteDirectory((string) $directorio);
        }

        foreach ((array) config('demo.storage.private', []) as $directorio) {
            Storage::disk('local')->deleteDirectory((string) $directorio);
        }
    }
}
