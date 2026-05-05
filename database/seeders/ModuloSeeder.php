<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Crea los modulos contratables/activables del sistema.
     */
    public function run(): void
    {
        foreach ($this->modulos() as $modulo) {
            Modulo::query()->updateOrCreate(
                ['clave' => $modulo['clave']],
                $modulo,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function modulos(): array
    {
        return [
            [
                'clave' => 'inventario',
                'nombre' => 'Inventario',
                'descripcion' => 'Gestion de productos, proveedores, ubicaciones, stock y movimientos.',
                'grupo' => 'panel',
                'activo' => true,
                'orden' => 10,
            ],
            [
                'clave' => 'compras',
                'nombre' => 'Compras a proveedor',
                'descripcion' => 'Pedidos, recepciones, incidencias, devoluciones y propuestas de compra.',
                'grupo' => 'panel',
                'activo' => true,
                'orden' => 20,
            ],
            [
                'clave' => 'web_publica',
                'nombre' => 'Web publica',
                'descripcion' => 'Pagina web gestionable conectada al panel de administracion.',
                'grupo' => 'web',
                'activo' => true,
                'orden' => 30,
            ],
            [
                'clave' => 'blog',
                'nombre' => 'Blog',
                'descripcion' => 'Noticias, articulos y categorias editoriales dentro de la web publica.',
                'grupo' => 'web',
                'activo' => true,
                'orden' => 40,
            ],
            [
                'clave' => 'ventas',
                'nombre' => 'Ventas',
                'descripcion' => 'Comandas de sala/barra conectadas con carta e inventario.',
                'grupo' => 'panel',
                'activo' => true,
                'orden' => 50,
            ],
            [
                'clave' => 'espacios',
                'nombre' => 'Espacios y mesas',
                'descripcion' => 'Gestion de recintos, zonas y mesas para separar sala de inventario.',
                'grupo' => 'panel',
                'activo' => true,
                'orden' => 52,
            ],
            [
                'clave' => 'personal',
                'nombre' => 'Gestion de personal',
                'descripcion' => 'Alta de usuarios operativos y permisos por rol.',
                'grupo' => 'panel',
                'activo' => true,
                'orden' => 55,
            ],
            [
                'clave' => 'reservas',
                'nombre' => 'Reservas',
                'descripcion' => 'Modulo futuro para gestionar reservas desde la web.',
                'grupo' => 'web',
                'activo' => false,
                'orden' => 60,
            ],
            [
                'clave' => 'lectura_documentos',
                'nombre' => 'Lectura asistida de documentos',
                'descripcion' => 'Modulo futuro para OCR/IA de albaranes y facturas.',
                'grupo' => 'compras',
                'activo' => false,
                'orden' => 70,
            ],
        ];
    }
}
