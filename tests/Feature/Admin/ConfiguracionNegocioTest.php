<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConfiguracionNegocioTest extends TestCase
{
    use RefreshDatabase;

    public function test_propietario_puede_actualizar_la_configuracion_del_negocio(): void
    {
        $propietario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);

        $respuesta = $this->actingAs($propietario)
            ->put(route('admin.configuracion.negocio.update'), $this->datosValidos());

        $respuesta->assertRedirect(route('admin.configuracion.negocio.edit'));
        $this->assertDatabaseHas('configuraciones_negocio', [
            'clave' => ConfiguracionNegocio::CLAVE_PRINCIPAL,
            'nombre_comercial' => 'Bar Demo',
            'localidad' => 'Sevilla',
            'moneda' => 'EUR',
        ]);
    }

    public function test_camarero_no_puede_gestionar_la_configuracion_del_negocio(): void
    {
        $camarero = Usuario::factory()->create(['rol' => RolUsuario::Camarero]);

        $this->actingAs($camarero)
            ->get(route('admin.configuracion.negocio.edit'))
            ->assertForbidden();

        $this->actingAs($camarero)
            ->put(route('admin.configuracion.negocio.update'), $this->datosValidos())
            ->assertForbidden();
    }

    public function test_el_panel_muestra_el_nombre_comercial_configurado(): void
    {
        ConfiguracionNegocio::query()->create([
            'clave' => ConfiguracionNegocio::CLAVE_PRINCIPAL,
            'nombre_comercial' => 'Bar Demo',
            'pais' => 'Espana',
            'zona_horaria' => 'Europe/Madrid',
            'moneda' => 'EUR',
        ]);
        $propietario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);

        $this->actingAs($propietario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Bar Demo');
    }

    public function test_propietario_puede_configurar_recursos_marca_y_seo(): void
    {
        Storage::fake('public');
        $propietario = Usuario::factory()->create(['rol' => RolUsuario::Propietario]);
        $datos = $this->datosValidos() + [
            'logo' => UploadedFile::fake()->image('logo.png', 600, 300),
            'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
            'imagen_social' => UploadedFile::fake()->image('social.jpg', 1200, 630),
        ];

        $this->actingAs($propietario)
            ->put(route('admin.configuracion.negocio.update'), $datos)
            ->assertRedirect(route('admin.configuracion.negocio.edit'));

        $configuracion = ConfiguracionNegocio::query()->firstOrFail();
        Storage::disk('public')->assertExists($configuracion->logo_path);
        Storage::disk('public')->assertExists($configuracion->favicon_path);
        Storage::disk('public')->assertExists($configuracion->imagen_social_path);
        $this->assertSame('#112233', $configuracion->color_primario);
        $this->assertFalse($configuracion->seo_indexar);
    }

    /** @return array<string, string> */
    private function datosValidos(): array
    {
        return [
            'nombre_comercial' => 'Bar Demo',
            'razon_social' => 'Bar Demo Sociedad Limitada',
            'eslogan' => 'Cocina y barra del barrio.',
            'descripcion_corta' => 'Bar de tapas en Sevilla.',
            'telefono' => '600 123 456',
            'email' => 'hola@bardemo.test',
            'direccion' => 'Calle Ejemplo 10',
            'localidad' => 'Sevilla',
            'provincia' => 'Sevilla',
            'codigo_postal' => '41001',
            'pais' => 'Espana',
            'horario' => 'Martes a domingo: 12:00-00:00',
            'google_maps_url' => 'https://maps.google.com/?q=Bar+Demo',
            'zona_horaria' => 'Europe/Madrid',
            'moneda' => 'EUR',
            'color_primario' => '#112233',
            'color_secundario' => '#445566',
            'color_fondo' => '#101010',
            'color_superficie' => '#202020',
            'color_texto' => '#F5F5F5',
            'seo_titulo' => 'Bar Demo · Tapas en Sevilla',
            'seo_descripcion' => 'Bar de tapas de demostración situado en Sevilla.',
        ];
    }
}
