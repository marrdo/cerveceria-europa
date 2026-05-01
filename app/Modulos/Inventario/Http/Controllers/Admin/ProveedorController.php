<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Modulos\Inventario\Models\Proveedor;

class ProveedorController extends CatalogoInventarioController
{
    protected string $modelo = Proveedor::class;
    protected string $rutaBase = 'admin.inventario.proveedores';
    protected string $titulo = 'Proveedor';
}
