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
     * Crea la base editable de web publica e importa la carta real del bar.
     */
    public function run(): void
    {
        $this->asegurarModulo('web_publica', 'Web publica', 'Permite publicar una pagina web gestionable desde el panel de administracion.', 30);
        $this->asegurarModulo('blog', 'Blog', 'Permite publicar noticias, eventos y articulos en la web publica.', 40);

        $this->call(NumierCartaSeeder::class);
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
}
