<?php

namespace Database\Seeders;

use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\CategoriaBlog;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Models\Modulo;
use App\Modulos\WebPublica\Models\PostBlog;
use App\Modulos\WebPublica\Models\SeccionWeb;
use Illuminate\Database\Seeder;

class WebPublicaSeeder extends Seeder
{
    /**
     * Crea contenido inicial publicable para demo de la web.
     */
    public function run(): void
    {
        $this->asegurarModulo('web_publica', 'Web publica', 'Permite publicar una pagina web gestionable desde el panel de administracion.', 30);
        $this->asegurarModulo('blog', 'Blog', 'Permite publicar noticias, eventos y articulos en la web publica.', 40);

        $categoriasCarta = $this->crearCategoriasCarta();

        foreach ($this->contenidos($categoriasCarta) as $contenido) {
            $tarifas = $contenido['tarifas'] ?? [];
            unset($contenido['tarifas']);

            $contenidoWeb = ContenidoWeb::query()->updateOrCreate(
                ['slug' => $contenido['slug']],
                $contenido,
            );

            $contenidoWeb->tarifas()->delete();

            foreach ($tarifas as $orden => $tarifa) {
                $contenidoWeb->tarifas()->create([
                    'nombre' => $tarifa['nombre'],
                    'precio' => $tarifa['precio'],
                    'orden' => $orden,
                ]);
            }
        }

        $categoriaCervezas = CategoriaBlog::query()->updateOrCreate(
            ['slug' => 'cervezas'],
            [
                'nombre' => 'Cervezas',
                'descripcion' => 'Articulos sobre estilos, referencias invitadas y cultura cervecera.',
                'activo' => true,
                'orden' => 10,
            ],
        );

        $categoriaEventos = CategoriaBlog::query()->updateOrCreate(
            ['slug' => 'eventos'],
            [
                'nombre' => 'Eventos',
                'descripcion' => 'Noticias y actividades especiales del bar.',
                'activo' => true,
                'orden' => 20,
            ],
        );

        $post = PostBlog::query()->updateOrCreate(
            ['slug' => 'bienvenida-al-blog'],
            [
                'titulo' => 'Bienvenida al blog de Cerveceria Europa',
                'resumen' => 'Un espacio para hablar de cervezas invitadas, eventos y novedades del bar.',
                'contenido' => 'Este blog forma parte de un modulo opcional de la web publica. Se puede activar o desactivar desde el panel segun lo que tenga contratado el cliente.',
                'imagen' => 'https://images.unsplash.com/photo-1516458464372-eea4ab222b31?auto=format&fit=crop&w=1200&q=80',
                'autor' => 'Cerveceria Europa',
                'publicado' => true,
                'destacado' => true,
                'publicado_at' => now(),
            ],
        );
        $post->categorias()->sync([$categoriaCervezas->id, $categoriaEventos->id]);

        SeccionWeb::query()->updateOrCreate(
            ['clave' => 'contacto'],
            [
                'nombre' => 'Contacto',
                'titulo' => 'Ven a Cerveceria Europa',
                'subtitulo' => 'Cervezas de importacion, artesanas y cocina de bar para compartir.',
                'contenido' => 'Consulta disponibilidad de fuera de carta y recomendaciones directamente en el local.',
                'datos' => [
                    'ubicacion' => 'Sevilla',
                    'reservas' => 'Llamanos o escribenos para consultar disponibilidad.',
                    'horario' => 'Horario pendiente de definir por el bar.',
                ],
                'activo' => true,
            ],
        );
    }

    /**
     * Asegura que existe un modulo sin pisar su estado activo/inactivo.
     */
    private function asegurarModulo(string $clave, string $nombre, string $descripcion, int $orden): void
    {
        $modulo = Modulo::query()->firstOrCreate(
            ['clave' => $clave],
            [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'grupo' => 'web',
                'activo' => true,
                'orden' => $orden,
            ],
        );

        $modulo->update([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'grupo' => 'web',
            'orden' => $orden,
        ]);
    }

    /**
     * Crea la estructura inicial de carta con categorias padre e hijas.
     *
     * @return array<string, CategoriaCarta>
     */
    private function crearCategoriasCarta(): array
    {
        $comida = $this->categoriaCarta('comidas', 'Comidas', 'Cocina de bar para compartir y maridar con cerveza.', 10);
        $cervezas = $this->categoriaCarta('cervezas', 'Cervezas', 'Importacion, artesanas y referencias invitadas.', 20);
        $bebidas = $this->categoriaCarta('bebidas', 'Bebidas', 'Refrescos, vinos, copas, cafe e infusiones.', 30);

        return [
            'comida' => $comida,
            'bebidas' => $bebidas,
            'cervezas' => $cervezas,
            'platos-frios' => $this->categoriaCarta('platos-frios', 'Platos frios', '', 1, $comida),
            'fuera-de-carta-comidas' => $this->categoriaCarta('fuera-de-carta-comidas', 'Fuera de carta', 'Pregunte siempre por nuestras novedades.', 2, $comida),
            'elaboraciones-con-cerveza' => $this->categoriaCarta('elaboraciones-con-cerveza', 'Elaboraciones con cerveza', 'Recetas originales elaboradas con cerveza.', 5, $comida),
            'patatas-y-huevos' => $this->categoriaCarta('patatas-y-huevos', 'Patatas y huevos', '', 6, $comida),
            'del-mar-a-la-plancha' => $this->categoriaCarta('del-mar-a-la-plancha', 'Del mar a la plancha', '', 7, $comida),
            'platos-internacionales' => $this->categoriaCarta('platos-internacionales', 'Platos internacionales', '', 8, $comida),
            'entre-panes' => $this->categoriaCarta('entre-panes', 'Entre panes', '', 9, $comida),
            'nuestros-fritos' => $this->categoriaCarta('nuestros-fritos', 'Nuestros fritos', '', 10, $comida),
            'carnes' => $this->categoriaCarta('carnes', 'Carnes', '', 11, $comida),
            'de-los-fogones' => $this->categoriaCarta('de-los-fogones', 'De los fogones', '', 12, $comida),
            'para-los-peques' => $this->categoriaCarta('para-los-peques', 'Para los peques', '', 13, $comida),
            'postres' => $this->categoriaCarta('postres', 'Postres', '', 14, $comida),
            'gourmet' => $this->categoriaCarta('cervezas-gourmet', 'Gourmet', 'Disfruta de nuestras cervezas exclusivas.', 15, $cervezas),
            'barril' => $this->categoriaCarta('cervezas-de-barril', 'Cervezas de barril', 'Todas nuestras cervezas de tirador.', 19, $cervezas),
            'novedades' => $this->categoriaCarta('cervezas-novedades', 'Novedades', '', 21, $cervezas),
            'ipas-y-pales-ales' => $this->categoriaCarta('ipas-y-pales-ales', 'IPAs y Pale Ales', 'Amargas, afrutadas y refrescantes.', 23, $cervezas),
            'lager-fuerte-y-bock' => $this->categoriaCarta('lager-fuerte-y-bock', 'Lager fuerte y Bock', 'Complejas, maltosas y con cuerpo.', 24, $cervezas),
            'lager-y-pilsen' => $this->categoriaCarta('lager-y-pilsen', 'Lager y Pilsen', 'Ligeras, refrescantes y suaves.', 25, $cervezas),
            'rio-azul' => $this->categoriaCarta('cervezas-rio-azul', 'Cervezas Rio Azul', '', 26, $cervezas),
            'blonde-belgian-ale' => $this->categoriaCarta('blonde-belgian-ale', 'Blonde Belgian Ale', 'Rubias belgas frutales y agridulces.', 28, $cervezas),
            'dobles-red-ale-tostadas' => $this->categoriaCarta('dobles-red-ale-tostadas', 'Dobles - Red Ale - Tostadas', 'Amargas, acarameladas y maltosas.', 29, $cervezas),
            'tripels-rubias-fuertes' => $this->categoriaCarta('tripels-rubias-fuertes', 'Tripels rubias fuertes', 'Rubias intensas, especiadas y citricas.', 30, $cervezas),
            'quadrupels-fuertes' => $this->categoriaCarta('quadrupels-fuertes', 'Quadrupels - Fuertes', 'Licorosas, dulces y complejas.', 31, $cervezas),
            'negras-y-stout-imperials' => $this->categoriaCarta('negras-y-stout-imperials', 'Negras y Stout Imperials', 'Sabores a cafe, regaliz y cacao.', 32, $cervezas),
            'fruit-beer' => $this->categoriaCarta('fruit-beer', 'Fruit Beer', 'Cervezas aromatizadas con frutas.', 33, $cervezas),
            'especiales' => $this->categoriaCarta('cervezas-especiales', 'Especiales', 'Referencias singulares de la carta.', 34, $cervezas),
            'sin-alcohol-sin-gluten' => $this->categoriaCarta('sin-alcohol-o-sin-gluten', 'Sin alcohol o sin gluten', '', 36, $cervezas),
            'sidras' => $this->categoriaCarta('sidras', 'Sidras', '', 37, $cervezas),
            'vinos' => $this->categoriaCarta('vinos', 'Vinos', '', 38, $bebidas),
            'otros-vinos' => $this->categoriaCarta('otros-vinos', 'Otros vinos', '', 39, $bebidas),
            'copas' => $this->categoriaCarta('copas', 'Copas', '', 40, $bebidas),
            'refrescos' => $this->categoriaCarta('refrescos', 'Refrescos', '', 42, $bebidas),
            'cafe-y-te' => $this->categoriaCarta('cafe-y-te', 'Cafe y te', '', 43, $bebidas),
        ];
    }

    /**
     * Crea o actualiza una categoria de carta.
     */
    private function categoriaCarta(string $slug, string $nombre, string $descripcion, int $orden, ?CategoriaCarta $padre = null): CategoriaCarta
    {
        return CategoriaCarta::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'categoria_padre_id' => $padre?->id,
                'nombre' => $nombre,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'activo' => true,
                'orden' => $orden,
            ],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function contenidos(array $categoriasCarta): array
    {
        return [
            [
                'tipo' => TipoContenidoWeb::Plato,
                'categoria_carta_id' => $categoriasCarta['entre-panes']->id,
                'titulo' => 'Tosta de presa iberica y queso curado',
                'slug' => 'tosta-presa-iberica-queso-curado',
                'descripcion_corta' => 'Pan crujiente, presa iberica, queso curado y toque de mostaza antigua.',
                'contenido' => 'Pensada para maridar con cervezas ambar, tostadas o belgas de cuerpo medio.',
                'precio' => 8.50,
                'tarifas' => [
                    ['nombre' => 'Tapa', 'precio' => 4.50],
                    ['nombre' => 'Plato', 'precio' => 8.50],
                ],
                'alergenos' => ['gluten', 'lacteos', 'mostaza'],
                'imagen' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80',
                'destacado' => true,
                'fuera_carta' => false,
                'publicado' => true,
                'orden' => 10,
            ],
            [
                'tipo' => TipoContenidoWeb::Plato,
                'categoria_carta_id' => $categoriasCarta['nuestros-fritos']->id,
                'titulo' => 'Croquetas de jamon al corte',
                'slug' => 'croquetas-jamon-corte',
                'descripcion_corta' => 'Cremosas, doradas y servidas al momento.',
                'contenido' => 'Una racion sencilla y directa para compartir en barra.',
                'precio' => 7.00,
                'tarifas' => [
                    ['nombre' => 'Tapa', 'precio' => 4.50],
                    ['nombre' => 'Plato', 'precio' => 7.00],
                ],
                'alergenos' => ['gluten', 'lacteos', 'huevo'],
                'imagen' => 'https://images.unsplash.com/photo-1562967916-eb82221dfb92?auto=format&fit=crop&w=900&q=80',
                'destacado' => false,
                'fuera_carta' => true,
                'publicado' => true,
                'orden' => 20,
            ],
            [
                'tipo' => TipoContenidoWeb::Cerveza,
                'categoria_carta_id' => $categoriasCarta['barril']->id,
                'titulo' => 'Leffe Blonde',
                'slug' => 'leffe-blonde',
                'descripcion_corta' => 'Belgian blonde suave, especiada y con final ligeramente dulce.',
                'contenido' => 'Buena entrada para quien quiere probar cerveza belga sin irse a perfiles extremos.',
                'precio' => 4.20,
                'tarifas' => [
                    ['nombre' => '25cl', 'precio' => 3.30],
                    ['nombre' => '33cl', 'precio' => 4.20],
                    ['nombre' => '50cl', 'precio' => 5.50],
                ],
                'alergenos' => ['gluten'],
                'imagen' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?auto=format&fit=crop&w=900&q=80',
                'destacado' => true,
                'fuera_carta' => false,
                'publicado' => true,
                'orden' => 30,
            ],
            [
                'tipo' => TipoContenidoWeb::Cerveza,
                'categoria_carta_id' => $categoriasCarta['novedades']->id,
                'titulo' => 'IPA invitada de la semana',
                'slug' => 'ipa-invitada-semana',
                'descripcion_corta' => 'Rotacion lupulada con perfil citrico y final seco.',
                'contenido' => 'La cerveza invitada cambia segun disponibilidad y entrada de proveedores.',
                'precio' => 4.80,
                'tarifas' => [
                    ['nombre' => '33cl', 'precio' => 4.80],
                    ['nombre' => '50cl', 'precio' => 6.50],
                ],
                'alergenos' => ['gluten'],
                'imagen' => 'https://images.unsplash.com/photo-1618885472179-5e474019f2a9?auto=format&fit=crop&w=900&q=80',
                'destacado' => true,
                'fuera_carta' => true,
                'publicado' => true,
                'orden' => 40,
            ],
            [
                'tipo' => TipoContenidoWeb::RecomendacionChef,
                'titulo' => 'Maridaje de la semana',
                'slug' => 'maridaje-semana',
                'descripcion_corta' => 'Croquetas de jamon con cerveza rubia belga.',
                'contenido' => 'Contraste entre cremosidad, salinidad y carbonatacion media.',
                'precio' => null,
                'alergenos' => null,
                'imagen' => 'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=900&q=80',
                'destacado' => true,
                'fuera_carta' => false,
                'publicado' => true,
                'orden' => 50,
            ],
        ];
    }
}
