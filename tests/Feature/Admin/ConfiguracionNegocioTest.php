<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ];
    }
}
