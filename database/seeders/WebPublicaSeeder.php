<?php

namespace Database\Seeders;

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
        $this->call(ModuloSeeder::class);
        $this->call(ConfiguracionNegocioSeeder::class);
        $this->call(CartaDemoSeeder::class);
        $this->crearBlogInicial();
        $this->crearSeccionesEditables();
    }

    /** Crea contenido editorial inicial del blog. */
    private function crearBlogInicial(): void
    {
        $categoriaCocina = CategoriaBlog::query()->updateOrCreate(
            ['slug' => 'cocina'],
            ['nombre' => 'Cocina', 'descripcion' => 'Recetas, producto y novedades de la cocina.', 'activo' => true, 'orden' => 10],
        );
        $categoriaEventos = CategoriaBlog::query()->updateOrCreate(
            ['slug' => 'novedades'],
            ['nombre' => 'Novedades', 'descripcion' => 'Noticias, horarios especiales y actividades del local.', 'activo' => true, 'orden' => 20],
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
     * Crea la estructura inicial sin sobrescribir los textos editados por el negocio.
     */
    private function crearSeccionesEditables(): void
    {
        $secciones = [
            'inicio_hero' => [
                'nombre' => 'Portada · Cabecera',
                'titulo' => 'La Plaza Demo',
                'subtitulo' => 'Sabores para compartir, momentos para volver',
                'contenido' => 'Cocina cercana, bebidas y una carta preparada para disfrutar sin prisas.',
                'datos' => ['eyebrow' => 'Bar y cocina en Sevilla', 'cta_principal' => 'Ver la carta', 'cta_secundaria' => 'Recomendaciones'],
            ],
            'inicio_sugerencias' => [
                'nombre' => 'Portada · Sugerencias',
                'titulo' => 'Lo que recomendamos hoy',
                'subtitulo' => 'Fuera de carta · disponibilidad limitada',
            ],
            'inicio_destacados' => [
                'nombre' => 'Portada · Destacados',
                'titulo' => 'Una carta sencilla y clara',
                'subtitulo' => 'Selección destacada',
            ],
            'inicio_valores' => [
                'nombre' => 'Portada · Valores',
                'titulo' => 'Una experiencia pensada para compartir',
                'subtitulo' => 'Nuestra forma de trabajar',
                'datos' => [
                    'valor_1_titulo' => 'Producto cercano',
                    'valor_1_descripcion' => 'Una carta cuidada que se adapta a cada temporada.',
                    'valor_2_titulo' => 'Servicio atento',
                    'valor_2_descripcion' => 'Un equipo que conoce la carta y te ayuda a elegir.',
                    'valor_3_titulo' => 'Ambiente propio',
                    'valor_3_descripcion' => 'Un lugar cómodo para desayunar, comer o tomar algo.',
                ],
            ],
            'contacto' => [
                'nombre' => 'Contacto',
                'titulo' => 'Ven a conocernos',
                'subtitulo' => 'Estamos deseando recibirte',
                'contenido' => 'Consulta nuestro horario y contacta con el equipo para preparar tu visita.',
                'datos' => ['reservas' => 'Llámanos o escríbenos para reservar.'],
            ],
        ];

        foreach ($secciones as $clave => $atributos) {
            SeccionWeb::query()->firstOrCreate(
                ['clave' => $clave],
                [...$atributos, 'activo' => true],
            );
        }
    }
}
