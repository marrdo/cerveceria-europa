<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Modulos\Inventario\Models\CategoriaProducto;

class CategoriaProductoController extends CatalogoInventarioController
{
    protected string $modelo = CategoriaProducto::class;
    protected string $rutaBase = 'admin.inventario.categorias';
    protected string $titulo = 'Categoria';
}
