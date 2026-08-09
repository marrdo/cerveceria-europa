<?php

namespace Tests\Feature\Entrega;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Support\Demo\RestauradorDemo;
use Database\Seeders\ModuloSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Mockery\MockInterface;
use Tests\TestCase;

class CalidadEntregaTest extends TestCase
{
    use RefreshDatabase;

    public function test_respuestas_web_incluyen_cabeceras_de_seguridad(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_respuesta_autenticada_no_se_almacena_en_cache(): void
    {
        $this->seed(ModuloSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Superadmin]);

        $this->actingAs($usuario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_cabeceras_del_panel_renderizan_un_titulo_h1(): void
    {
        $this->seed(ModuloSeeder::class);
        $usuario = Usuario::factory()->create(['rol' => RolUsuario::Superadmin]);

        $this->actingAs($usuario)
            ->get(route('admin.inventario.index'))
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Inventario');
    }

    public function test_produccion_https_activa_hsts(): void
    {
        $this->forzarProduccion();

        $this->withServerVariables(['HTTPS' => 'on', 'SERVER_PORT' => 443])
            ->get('https://localhost/login')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_recuperacion_de_password_esta_limitada(): void
    {
        RateLimiter::clear('tests');

        foreach (range(1, 5) as $intento) {
            $this->post(route('password.email'), ['email' => 'nadie'.$intento.'@example.com'])
                ->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => 'nadie@example.com'])
            ->assertTooManyRequests();
    }

    public function test_restauracion_demo_se_bloquea_en_produccion(): void
    {
        $this->forzarProduccion();
        config()->set('demo.enabled', true);

        $this->artisan('demo:restaurar', ['--force' => true])
            ->expectsOutput('La restauración de la demo está bloqueada en producción.')
            ->assertFailed();
    }

    public function test_restauracion_exige_coincidencia_exacta_de_base(): void
    {
        config()->set('demo.enabled', true);
        config()->set('demo.database', 'otra_base');

        $this->artisan('demo:restaurar', ['--force' => true])
            ->assertFailed();
    }

    public function test_restauracion_autorizada_delega_en_el_servicio(): void
    {
        $conexion = (string) config('database.default');
        $base = (string) config("database.connections.{$conexion}.database");
        config()->set('demo.enabled', true);
        config()->set('demo.database', $base);
        $this->mock(RestauradorDemo::class, function (MockInterface $mock): void {
            $mock->shouldReceive('ejecutar')->once()->andReturn(0);
        });

        $this->artisan('demo:restaurar', ['--force' => true])
            ->expectsOutput('Demo restaurada: migraciones, seeders y almacenamiento vuelven al estado inicial.')
            ->assertSuccessful();
    }

    public function test_auditoria_local_supera_requisitos_tecnicos(): void
    {
        $this->seed(ModuloSeeder::class);

        $this->artisan('app:auditar-entrega')->assertSuccessful();
    }

    public function test_auditoria_rechaza_una_configuracion_de_produccion_insegura(): void
    {
        $this->seed(ModuloSeeder::class);
        $this->forzarProduccion();
        config()->set('app.debug', true);
        config()->set('app.url', 'http://example.com');
        config()->set('demo.enabled', true);
        config()->set('session.secure', false);
        config()->set('session.encrypt', false);
        config()->set('mail.default', 'log');
        config()->set('demo.superadmin.password', 'password');

        $this->artisan('app:auditar-entrega')->assertFailed();
    }

    private function forzarProduccion(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config()->set('app.env', 'production');
    }
}
