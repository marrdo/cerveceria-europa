<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Modulos\Inventario\Models\UbicacionInventario;

class UbicacionInventarioController extends CatalogoInventarioController
{
    protected string $modelo = UbicacionInventario::class;
    protected string $rutaBase = 'admin.inventario.ubicaciones';
    protected string $titulo = 'Ubicacion';

    protected function usaSlug(): bool
    {
        return false;
    }
}
