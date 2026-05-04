<?php

namespace Tests\Feature\WebPublica;

use App\Enums\RolUsuario;
use App\Modulos\Inventario\Models\CategoriaProducto;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\StockInventario;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Inventario\Models\UnidadInventario;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\CategoriaBlog;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Modulos\WebPublica\Models\PostBlog;
use App\Modulos\WebPublica\Models\SeccionWeb;
use App\Models\Modulo;
use App\Models\Usuario;
use Database\Seeders\WebPublicaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebPublicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Modulo::query()->create([
            'clave' => 'web_publica',
            'nombre' => 'Modulo Web Publica',
            'activo' => true,
        ]);
    }

    public function test_public_home_renders_published_content(): void
    {
        $this->seed(WebPublicaSeeder::class);

        $this->get(route('web.inicio'))
            ->assertOk()
            ->assertSee('Cerveceria Europa')
            ->assertSee('Leffe Blonde');
    }

    public function test_public_web_hides_unpublished_content(): void
    {
        ContenidoWeb::query()->create([
            'tipo' => TipoContenidoWeb::Plato,
            'titulo' => 'Plato oculto',
            'slug' => 'plato-oculto',
            'descripcion_corta' => 'No debe verse',
            'publicado' => false,
        ]);

        $this->get(route('web.carta'))
            ->assertOk()
            ->assertDontSee('Plato oculto');
    }

    public function test_owner_can_create_public_web_content_from_admin(): void
    {
        Storage::fake('public');
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        $producto = $this->crearProductoConStock(12);
        $imagen = UploadedFile::fake()->image('plato.jpg', 900, 600);

        $this->actingAs($usuario)
            ->post(route('admin.web-publica.contenidos.store'), [
                'tipo' => TipoContenidoWeb::Plato->value,
                'producto_id' => $producto->id,
                'titulo' => 'Ensaladilla de la casa',
                'descripcion_corta' => 'Clasica, fria y perfecta con cerveza rubia.',
                'contenido' => 'Receta de la casa para compartir.',
                'precio' => '6.50',
                'tarifas' => [
                    ['nombre' => 'Tapa', 'precio' => '4.50'],
                    ['nombre' => 'Plato', 'precio' => '6.50'],
                ],
                'alergenos' => 'huevo, pescado',
                'imagen' => $imagen,
                'publicado' => '1',
                'destacado' => '1',
                'fuera_carta' => '1',
                'orden' => '1',
            ])
            ->assertRedirect(route('admin.web-publica.contenidos.index'));

        $contenido = ContenidoWeb::query()->firstOrFail();

        $this->assertSame('ensaladilla-de-la-casa', $contenido->slug);
        $this->assertSame($producto->id, $contenido->producto_id);
        $this->assertSame(['huevo', 'pescado'], $contenido->alergenos);
        $this->assertTrue($contenido->publicado);
        $this->assertTrue($contenido->destacado);
        $this->assertTrue($contenido->fuera_carta);
        $this->assertSame(2, $contenido->tarifas()->count());
        Storage::disk('public')->assertExists($contenido->imagen);
    }

    public function test_manager_cannot_access_public_web_admin_module(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Encargado]);

        $this->actingAs($usuario)
            ->get(route('admin.web-publica.contenidos.index'))
            ->assertForbidden();
    }

    public function test_public_web_module_can_be_disabled_for_public_routes_and_owner_admin(): void
    {
        $propietario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        Modulo::query()->where('clave', 'web_publica')->update(['activo' => false]);

        $this->get(route('web.inicio'))->assertNotFound();

        $this->actingAs($propietario)
            ->get(route('admin.web-publica.contenidos.index'))
            ->assertForbidden();
    }

    public function test_superadmin_can_access_public_web_admin_even_when_module_is_disabled(): void
    {
        $superadmin = Usuario::factory()->create(['rol' => RolUsuario::Superadmin]);
        Modulo::query()->where('clave', 'web_publica')->update(['activo' => false]);

        $this->actingAs($superadmin)
            ->get(route('admin.web-publica.contenidos.index'))
            ->assertOk()
            ->assertSee('Activar web publica');
    }

    public function test_owner_cannot_toggle_main_public_web_module(): void
    {
        $propietario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        $modulo = Modulo::query()->where('clave', 'web_publica')->firstOrFail();

        $this->actingAs($propietario)
            ->patch(route('admin.modulos.toggle', $modulo))
            ->assertForbidden();
    }

    public function test_linked_product_without_stock_is_hidden_from_public_menu(): void
    {
        $productoSinStock = $this->crearProductoConStock(0);
        $productoConStock = $this->crearProductoConStock(4);

        ContenidoWeb::query()->create([
            'tipo' => TipoContenidoWeb::Cerveza,
            'producto_id' => $productoSinStock->id,
            'titulo' => 'Cerveza agotada',
            'slug' => 'cerveza-agotada',
            'publicado' => true,
        ]);

        ContenidoWeb::query()->create([
            'tipo' => TipoContenidoWeb::Cerveza,
            'producto_id' => $productoConStock->id,
            'titulo' => 'Cerveza disponible',
            'slug' => 'cerveza-disponible',
            'publicado' => true,
        ]);

        $this->get(route('web.cervezas'))
            ->assertOk()
            ->assertSee('Cerveza disponible')
            ->assertDontSee('Cerveza agotada');
    }

    public function test_blog_module_can_be_disabled(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Superadmin]);
        $modulo = Modulo::query()->updateOrCreate(['clave' => 'blog'], [
            'clave' => 'blog',
            'nombre' => 'Modulo Blog',
            'activo' => true,
        ]);

        PostBlog::query()->create([
            'titulo' => 'Post visible',
            'slug' => 'post-visible',
            'contenido' => 'Contenido del post',
            'publicado' => true,
            'publicado_at' => now(),
        ]);

        $this->get(route('web.blog'))
            ->assertOk()
            ->assertSee('Post visible');

        $this->actingAs($usuario)
            ->patch(route('admin.modulos.toggle', $modulo))
            ->assertRedirect();

        $this->get(route('web.blog'))->assertNotFound();
        $this->actingAs($usuario)
            ->get(route('admin.web-publica.blog.index'))
            ->assertNotFound();
    }

    public function test_blog_posts_are_managed_in_their_own_table(): void
    {
        Storage::fake('public');
        Modulo::query()->create([
            'clave' => 'blog',
            'nombre' => 'Modulo Blog',
            'activo' => true,
        ]);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        $categoria = CategoriaBlog::query()->create([
            'nombre' => 'Cervezas',
            'slug' => 'cervezas',
            'activo' => true,
            'orden' => 1,
        ]);

        $this->actingAs($usuario)
            ->post(route('admin.web-publica.blog.store'), [
                'titulo' => 'Nueva cerveza invitada',
                'categorias' => [$categoria->id],
                'resumen' => 'Esta semana entra una nueva referencia.',
                'contenido' => 'Contenido completo del post.',
                'autor' => 'Cerveceria Europa',
                'publicado' => '1',
                'destacado' => '1',
                'publicado_at' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('admin.web-publica.blog.index'));

        $this->assertDatabaseHas('posts_blog', [
            'slug' => 'nueva-cerveza-invitada',
            'publicado' => true,
        ]);
        $post = PostBlog::query()->where('slug', 'nueva-cerveza-invitada')->firstOrFail();
        $this->assertTrue($post->categorias()->whereKey($categoria->id)->exists());

        $this->assertDatabaseMissing('contenidos_web', [
            'slug' => 'nueva-cerveza-invitada',
        ]);
    }

    public function test_public_blog_can_be_filtered_by_category(): void
    {
        Modulo::query()->create([
            'clave' => 'blog',
            'nombre' => 'Modulo Blog',
            'activo' => true,
        ]);
        $cervezas = CategoriaBlog::query()->create([
            'nombre' => 'Cervezas',
            'slug' => 'cervezas',
            'activo' => true,
            'orden' => 1,
        ]);
        $comida = CategoriaBlog::query()->create([
            'nombre' => 'Comida',
            'slug' => 'comida',
            'activo' => true,
            'orden' => 2,
        ]);
        $postCervezas = PostBlog::query()->create([
            'titulo' => 'Post de cerveza',
            'slug' => 'post-de-cerveza',
            'contenido' => 'Contenido cerveza',
            'publicado' => true,
            'publicado_at' => now(),
        ]);
        $postComida = PostBlog::query()->create([
            'titulo' => 'Post de comida',
            'slug' => 'post-de-comida',
            'contenido' => 'Contenido comida',
            'publicado' => true,
            'publicado_at' => now(),
        ]);
        $postCervezas->categorias()->sync([$cervezas->id]);
        $postComida->categorias()->sync([$comida->id]);

        $this->get(route('web.blog.categoria', $cervezas))
            ->assertOk()
            ->assertSee('Post de cerveza')
            ->assertDontSee('Post de comida');
    }

    public function test_contact_section_is_editable_from_admin(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        $seccion = SeccionWeb::query()->create([
            'clave' => 'contacto',
            'nombre' => 'Contacto',
            'titulo' => 'Ven a Cerveceria Europa',
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->put(route('admin.web-publica.secciones.update', $seccion), [
                'titulo' => 'Estamos en Triana',
                'subtitulo' => 'Cerveza fria y cocina para compartir.',
                'contenido' => 'Texto editable por el bar.',
                'ubicacion' => 'Calle Betis, Sevilla',
                'reservas' => 'WhatsApp 600 000 000',
                'horario' => 'Lunes a domingo de 12:00 a 00:00',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.web-publica.secciones.index'));

        $this->get(route('web.contacto'))
            ->assertOk()
            ->assertSee('Estamos en Triana')
            ->assertSee('WhatsApp 600 000 000')
            ->assertSee('Lunes a domingo de 12:00 a 00:00');
    }

    public function test_public_menu_is_grouped_by_editable_menu_categories(): void
    {
        $padre = CategoriaCarta::query()->create([
            'nombre' => 'Bebidas',
            'slug' => 'bebidas',
            'descripcion' => 'Bebidas de la casa',
            'activo' => true,
            'orden' => 1,
        ]);
        $hija = CategoriaCarta::query()->create([
            'categoria_padre_id' => $padre->id,
            'nombre' => 'Cervezas de barril',
            'slug' => 'cervezas-barril',
            'descripcion' => 'Grifos disponibles',
            'activo' => true,
            'orden' => 1,
        ]);

        ContenidoWeb::query()->create([
            'tipo' => TipoContenidoWeb::Cerveza,
            'categoria_carta_id' => $hija->id,
            'titulo' => 'Leffe de barril',
            'slug' => 'leffe-barril',
            'publicado' => true,
            'orden' => 1,
        ]);

        $this->get(route('web.carta'))
            ->assertOk()
            ->assertSee('Bebidas')
            ->assertSee('Cervezas de barril')
            ->assertSee('Leffe de barril');
    }

    public function test_owner_can_create_menu_category_with_parent_from_admin(): void
    {
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        $padre = CategoriaCarta::query()->create([
            'nombre' => 'Comida',
            'slug' => 'comida',
            'activo' => true,
            'orden' => 1,
        ]);

        $this->actingAs($usuario)
            ->post(route('admin.web-publica.carta-categorias.store'), [
                'categoria_padre_id' => $padre->id,
                'nombre' => 'Tapas frias',
                'descripcion' => 'Seccion de tapas frias',
                'activo' => '1',
                'orden' => '2',
            ])
            ->assertRedirect(route('admin.web-publica.carta-categorias.index'));

        $this->assertDatabaseHas('categorias_carta', [
            'categoria_padre_id' => $padre->id,
            'slug' => 'tapas-frias',
            'activo' => true,
        ]);
    }

    public function test_public_menu_shows_multiple_rates_for_content(): void
    {
        $categoria = CategoriaCarta::query()->create([
            'nombre' => 'Cervezas',
            'slug' => 'cervezas',
            'activo' => true,
            'orden' => 1,
        ]);
        $seccion = CategoriaCarta::query()->create([
            'categoria_padre_id' => $categoria->id,
            'nombre' => 'Cervezas de barril',
            'slug' => 'cervezas-barril',
            'activo' => true,
            'orden' => 1,
        ]);
        $contenido = ContenidoWeb::query()->create([
            'tipo' => TipoContenidoWeb::Cerveza,
            'categoria_carta_id' => $seccion->id,
            'titulo' => 'Leffe rubia',
            'slug' => 'leffe-rubia',
            'publicado' => true,
        ]);
        $contenido->tarifas()->createMany([
            ['nombre' => '25cl', 'precio' => 3.30, 'orden' => 1],
            ['nombre' => '50cl', 'precio' => 5.50, 'orden' => 2],
        ]);

        $this->get(route('web.carta'))
            ->assertOk()
            ->assertSee('Leffe rubia')
            ->assertSee('25cl')
            ->assertSee('3,30 EUR')
            ->assertSee('50cl')
            ->assertSee('5,50 EUR');
    }

    private function crearProductoConStock(float $cantidad): Producto
    {
        $categoria = CategoriaProducto::query()->create([
            'nombre' => 'Cervezas test',
            'slug' => 'cervezas-test-'.str()->random(5),
            'activo' => true,
        ]);

        $codigoUnidad = 'bot'.str()->random(5);
        $unidad = UnidadInventario::query()->create([
            'nombre' => 'Botella',
            'codigo' => $codigoUnidad,
            'permite_decimal' => false,
            'activo' => true,
        ]);

        $ubicacion = UbicacionInventario::query()->create([
            'nombre' => 'Almacen test',
            'codigo' => 'ALM'.str()->random(6),
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'categoria_producto_id' => $categoria->id,
            'unidad_inventario_id' => $unidad->id,
            'nombre' => 'Producto test '.str()->random(5),
            'sku' => 'WEB-'.str()->random(6),
            'precio_venta' => 4.50,
            'precio_coste' => 2.00,
            'controla_stock' => true,
            'controla_caducidad' => false,
            'cantidad_alerta_stock' => 1,
            'activo' => true,
        ]);

        StockInventario::query()->create([
            'producto_id' => $producto->id,
            'ubicacion_inventario_id' => $ubicacion->id,
            'cantidad' => $cantidad,
            'cantidad_minima' => 0,
        ]);

        return $producto;
    }
}
