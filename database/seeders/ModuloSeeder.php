<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Modulos\Sistema\Modulos\CatalogoModulos;
use App\Modulos\Sistema\Modulos\DefinicionModulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Sincroniza metadatos sin sobrescribir contratos ya activados o
     * desactivados en una instalación existente.
     */
    public function run(): void
    {
        app(CatalogoModulos::class)->todos()->each(
            function (DefinicionModulo $definicion): void {
                $atributos = $definicion->atributosPersistibles();
                $activoPorDefecto = $atributos['activo'];
                unset($atributos['activo']);

                Modulo::query()->updateOrCreate(
                    ['clave' => $definicion->clave],
                    [
                        ...$atributos,
                        'activo' => Modulo::query()->where('clave', $definicion->clave)->value('activo')
                            ?? $activoPorDefecto,
                    ],
                );
            },
        );
    }
}
