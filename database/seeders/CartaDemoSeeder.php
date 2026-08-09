<?php

namespace Database\Seeders;

use App\Modulos\Inventario\Models\Producto;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Modulos\WebPublica\Models\TarifaContenidoWeb;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea una carta ficticia sin depender de datos ni imágenes de terceros.
 */
class CartaDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->limpiarCarta();
            $categorias = $this->crearCategorias();

            foreach ($this->contenidos() as $orden => $datos) {
                $tarifas = $datos['tarifas'] ?? [];
                unset($datos['tarifas']);

                $categoria = $categorias[$datos['categoria']];
                unset($datos['categoria']);

                $sku = $datos['producto_sku'] ?? null;
                unset($datos['producto_sku']);

                $contenido = ContenidoWeb::query()->create([
                    ...$datos,
                    'categoria_carta_id' => $categoria->id,
                    'producto_id' => $sku ? Producto::query()->where('sku', $sku)->value('id') : null,
                    'imagen' => null,
                    'publicado' => true,
                    'orden' => $orden + 1,
                ]);

                foreach ($tarifas as $indice => $tarifa) {
                    $contenido->tarifas()->create([
                        ...$tarifa,
                        'orden' => $indice + 1,
                    ]);
                }
            }
        });
    }

    /**
     * Borra cualquier carta anterior para impedir que una demo conserve datos reales.
     */
    private function limpiarCarta(): void
    {
        TarifaContenidoWeb::query()->delete();
        ContenidoWeb::withTrashed()->forceDelete();
        CategoriaCarta::query()->update(['categoria_padre_id' => null]);
        CategoriaCarta::withTrashed()->forceDelete();
    }

    /** @return array<string, CategoriaCarta> */
    private function crearCategorias(): array
    {
        $definiciones = [
            'para-compartir' => ['padre' => 'Cocina', 'padre_slug' => 'cocina', 'padre_orden' => 10, 'nombre' => 'Para compartir', 'descripcion' => 'Entrantes y raciones pensadas para el centro de la mesa.'],
            'principales' => ['padre' => 'Cocina', 'padre_slug' => 'cocina', 'padre_orden' => 10, 'nombre' => 'Principales', 'descripcion' => 'Platos completos de elaboración sencilla.'],
            'postres' => ['padre' => 'Cocina', 'padre_slug' => 'cocina', 'padre_orden' => 10, 'nombre' => 'Postres', 'descripcion' => 'Finales dulces preparados para la demostración.'],
            'cervezas' => ['padre' => 'Bebidas', 'padre_slug' => 'bebidas', 'padre_orden' => 20, 'nombre' => 'Cervezas', 'descripcion' => 'Referencias ficticias en varios formatos.'],
            'refrescos' => ['padre' => 'Bebidas', 'padre_slug' => 'bebidas', 'padre_orden' => 20, 'nombre' => 'Refrescos', 'descripcion' => 'Bebidas frías y opciones sin alcohol.'],
            'cafes' => ['padre' => 'Bebidas', 'padre_slug' => 'bebidas', 'padre_orden' => 20, 'nombre' => 'Cafés e infusiones', 'descripcion' => 'Cafés, tés e infusiones.'],
        ];

        $padres = [];
        $categorias = [];

        foreach ($definiciones as $clave => $definicion) {
            $padre = $padres[$definicion['padre_slug']] ??= CategoriaCarta::query()->create([
                'nombre' => $definicion['padre'],
                'slug' => $definicion['padre_slug'],
                'descripcion' => $definicion['padre'] === 'Cocina'
                    ? 'Propuestas sencillas para comer y compartir.'
                    : 'Bebidas para cualquier momento del día.',
                'activo' => true,
                'orden' => $definicion['padre_orden'],
            ]);

            $categorias[$clave] = CategoriaCarta::query()->create([
                'categoria_padre_id' => $padre->id,
                'nombre' => $definicion['nombre'],
                'slug' => $clave,
                'descripcion' => $definicion['descripcion'],
                'activo' => true,
                'orden' => count($categorias) + 1,
            ]);
        }

        return $categorias;
    }

    /** @return array<int, array<string, mixed>> */
    private function contenidos(): array
    {
        return [
            [
                'categoria' => 'para-compartir',
                'tipo' => TipoContenidoWeb::Plato,
                'titulo' => 'Patatas bravas de la casa',
                'slug' => 'patatas-bravas-demo',
                'descripcion_corta' => 'Patata crujiente con salsa brava y alioli suave.',
                'precio' => 5.50,
                'alergenos' => ['huevo'],
                'destacado' => true,
                'fuera_carta' => false,
            ],
            [
                'categoria' => 'para-compartir',
                'tipo' => TipoContenidoWeb::Plato,
                'titulo' => 'Croquetas cremosas',
                'slug' => 'croquetas-cremosas-demo',
                'descripcion_corta' => 'Seis unidades con relleno de temporada.',
                'precio' => 8.00,
                'alergenos' => ['gluten', 'leche', 'huevo'],
                'destacado' => true,
                'fuera_carta' => false,
            ],
            [
                'categoria' => 'para-compartir',
                'tipo' => TipoContenidoWeb::RecomendacionChef,
                'titulo' => 'Arroz del día',
                'slug' => 'arroz-del-dia-demo',
                'descripcion_corta' => 'Sugerencia ficticia disponible hasta agotar existencias.',
                'precio' => 12.50,
                'alergenos' => null,
                'destacado' => true,
                'fuera_carta' => true,
            ],
            [
                'categoria' => 'principales',
                'tipo' => TipoContenidoWeb::Plato,
                'titulo' => 'Hamburguesa de la casa',
                'slug' => 'hamburguesa-de-la-casa-demo',
                'descripcion_corta' => 'Carne, queso, cebolla caramelizada y patatas.',
                'precio' => 12.90,
                'alergenos' => ['gluten', 'leche'],
                'destacado' => true,
                'fuera_carta' => false,
            ],
            [
                'categoria' => 'principales',
                'tipo' => TipoContenidoWeb::Plato,
                'titulo' => 'Ensalada templada',
                'slug' => 'ensalada-templada-demo',
                'descripcion_corta' => 'Verduras asadas, hojas verdes y vinagreta de cítricos.',
                'precio' => 10.50,
                'alergenos' => null,
                'destacado' => false,
                'fuera_carta' => false,
            ],
            [
                'categoria' => 'postres',
                'tipo' => TipoContenidoWeb::Plato,
                'titulo' => 'Tarta de queso',
                'slug' => 'tarta-de-queso-demo',
                'descripcion_corta' => 'Tarta cremosa horneada y servida con fruta.',
                'precio' => 5.50,
                'alergenos' => ['leche', 'huevo'],
                'destacado' => true,
                'fuera_carta' => false,
            ],
            [
                'categoria' => 'cervezas',
                'tipo' => TipoContenidoWeb::Cerveza,
                'producto_sku' => 'DEMO-CERV-001',
                'titulo' => 'Rubia de la casa',
                'slug' => 'rubia-de-la-casa-demo',
                'descripcion_corta' => 'Cerveza ficticia ligera y refrescante.',
                'precio' => null,
                'alergenos' => ['gluten'],
                'destacado' => true,
                'fuera_carta' => false,
                'tarifas' => [
                    ['nombre' => 'Caña', 'precio' => 2.80],
                    ['nombre' => 'Pinta', 'precio' => 4.80],
                ],
            ],
            [
                'categoria' => 'cervezas',
                'tipo' => TipoContenidoWeb::Cerveza,
                'titulo' => 'Tostada artesanal Demo',
                'slug' => 'tostada-artesanal-demo',
                'descripcion_corta' => 'Referencia ficticia de sabor tostado y final suave.',
                'precio' => 3.80,
                'alergenos' => ['gluten'],
                'destacado' => false,
                'fuera_carta' => false,
            ],
            [
                'categoria' => 'refrescos',
                'tipo' => TipoContenidoWeb::Bebida,
                'producto_sku' => 'DEMO-REF-001',
                'titulo' => 'Limonada casera',
                'slug' => 'limonada-casera-demo',
                'descripcion_corta' => 'Limón, hierbabuena y un toque de azúcar.',
                'precio' => 3.20,
                'alergenos' => null,
                'destacado' => true,
                'fuera_carta' => false,
            ],
            [
                'categoria' => 'cafes',
                'tipo' => TipoContenidoWeb::Bebida,
                'titulo' => 'Café de la casa',
                'slug' => 'cafe-de-la-casa-demo',
                'descripcion_corta' => 'Café solo, cortado o con leche.',
                'precio' => 1.60,
                'alergenos' => null,
                'destacado' => false,
                'fuera_carta' => false,
            ],
        ];
    }
}
