<?php

namespace Database\Seeders;

use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\Proveedor;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventarioSeeder extends Seeder
{
    /**
     * Crea catalogos iniciales utiles para un bar.
     */
    public function run(): void
    {
        foreach (['Cervezas', 'Refrescos', 'Vinos', 'Licores', 'Cafes e infusiones', 'Alimentacion', 'Limpieza'] as $nombre) {
            CategoriaProducto::query()->updateOrCreate(
                ['slug' => Str::slug($nombre)],
                ['nombre' => $nombre, 'descripcion' => null, 'activo' => true],
            );
        }

        foreach ($this->unidades() as $unidad) {
            UnidadInventario::query()->updateOrCreate(['codigo' => $unidad['codigo']], $unidad);
        }

        foreach ($this->ubicaciones() as $ubicacion) {
            UbicacionInventario::query()->updateOrCreate(['codigo' => $ubicacion['codigo']], $ubicacion);
        }

        Proveedor::query()->updateOrCreate(
            ['slug' => 'proveedor-generico'],
            ['nombre' => 'Proveedor generico', 'telefono' => null, 'email' => null, 'activo' => true],
        );

        $this->call(InventarioCervezasDemoSeeder::class);
    }

    /**
     * Unidades iniciales para inventario de bar.
     *
     * @return array<int, array<string, mixed>>
     */
    private function unidades(): array
    {
        return [
            ['nombre' => 'Unidad', 'codigo' => 'ud', 'permite_decimal' => false, 'descripcion' => 'Unidades enteras.', 'activo' => true],
            ['nombre' => 'Caja', 'codigo' => 'caja', 'permite_decimal' => false, 'descripcion' => 'Cajas completas.', 'activo' => true],
            ['nombre' => 'Botella', 'codigo' => 'botella', 'permite_decimal' => false, 'descripcion' => 'Botellas individuales.', 'activo' => true],
            ['nombre' => 'Barril', 'codigo' => 'barril', 'permite_decimal' => false, 'descripcion' => 'Barriles de cerveza.', 'activo' => true],
            ['nombre' => 'Litro', 'codigo' => 'l', 'permite_decimal' => true, 'descripcion' => 'Volumen en litros.', 'activo' => true],
            ['nombre' => 'Kilogramo', 'codigo' => 'kg', 'permite_decimal' => true, 'descripcion' => 'Peso en kilogramos.', 'activo' => true],
        ];
    }

    /**
     * Ubicaciones iniciales para Cerveceria Europa.
     *
     * @return array<int, array<string, mixed>>
     */
    private function ubicaciones(): array
    {
        return [
            ['nombre' => 'Barra', 'codigo' => 'BARRA', 'descripcion' => 'Stock operativo de barra.', 'activo' => true],
            ['nombre' => 'Almacen', 'codigo' => 'ALMACEN', 'descripcion' => 'Zona principal de almacenamiento.', 'activo' => true],
            ['nombre' => 'Camara fria', 'codigo' => 'CAMARA_FRIA', 'descripcion' => 'Productos refrigerados.', 'activo' => true],
            ['nombre' => 'Cocina', 'codigo' => 'COCINA', 'descripcion' => 'Productos usados en cocina.', 'activo' => true],
        ];
    }
}
