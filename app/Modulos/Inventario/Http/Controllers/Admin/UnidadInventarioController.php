<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Modulos\Inventario\Models\UnidadInventario;

class UnidadInventarioController extends CatalogoInventarioController
{
    protected string $modelo = UnidadInventario::class;
    protected string $rutaBase = 'admin.inventario.unidades';
    protected string $titulo = 'Unidad';

    protected function usaSlug(): bool
    {
        return false;
    }
}
