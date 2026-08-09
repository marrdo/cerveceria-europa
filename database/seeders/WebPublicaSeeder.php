<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Modulos\WebPublica\Models\CategoriaBlog;
use App\Modulos\WebPublica\Models\PostBlog;
use App\Modulos\WebPublica\Models\SeccionWeb;
use Illuminate\Database\Seeder;

class WebPublicaSeeder extends Seeder
{
    /**
     * Crea una web pública completamente ficticia para la demostración.
     */
    public function run(): void
    {
        $this->asegurarModulo('web_publica', 'Web publica', 'Permite publicar una pagina web gestionable desde el panel de administracion.', 30);
        $this->asegurarModulo('blog', 'Blog', 'Permite publicar noticias, eventos y articulos en la web publica.', 40);

        $this->call(ConfiguracionNegocioSeeder::class);
        $this->call(CartaDemoSeeder::class);
        $this->crearBlogInicial();
        $this->crearSeccionesEditables();
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
     * Crea contenido editorial inicial del blog.
     */
    private function crearBlogInicial(): void
    {
        $categoriaCocina = CategoriaBlog::query()->updateOrCreate(
            ['slug' => 'cocina'],
            [
                'nombre' => 'Cocina',
                'descripcion' => 'Recetas, producto y novedades de la cocina.',
                'activo' => true,
                'orden' => 10,
            ],
        );

        $categoriaEventos = CategoriaBlog::query()->updateOrCreate(
            ['slug' => 'novedades'],
            [
                'nombre' => 'Novedades',
                'descripcion' => 'Noticias, horarios especiales y actividades del local.',
                'activo' => true,
                'orden' => 20,
            ],
        );

        $post = PostBlog::query()->updateOrCreate(
            ['slug' => 'bienvenida-al-blog'],
            [
                'titulo' => 'Bienvenida a nuestro blog',
                'resumen' => 'Un espacio de demostración para publicar novedades, propuestas de cocina y eventos.',
                'contenido' => 'Este contenido es completamente ficticio. Sirve para comprobar cómo se administra y publica el blog desde el panel.',
                'imagen' => null,
                'autor' => null,
                'publicado' => true,
                'destacado' => true,
                'publicado_at' => now(),
            ],
        );

        $post->categorias()->sync([$categoriaCocina->id, $categoriaEventos->id]);
    }

    /**
     * Crea secciones estructurales editables de la web.
     */
    private function crearSeccionesEditables(): void
    {
        SeccionWeb::query()->updateOrCreate(
            ['clave' => 'contacto'],
            [
                'nombre' => 'Contacto',
                'titulo' => 'Ven a conocernos',
                'subtitulo' => 'Cocina cercana, bebidas y un espacio preparado para compartir.',
                'contenido' => 'Todos los datos de esta instalación son ficticios y pueden editarse desde el panel.',
                'datos' => [
                    'reservas' => 'Llámanos o escríbenos para probar el flujo de reservas.',
                ],
                'activo' => true,
            ],
        );
    }
}
