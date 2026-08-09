<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\JornadaLaboral;
use Database\Seeders\AreaTrabajoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanificacionTurnosModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_encargado_puede_crear_un_cuadrante_semanal(): void
    {
        $encargado = $this->usuario(RolUsuario::Encargado);
        $this->activarModulo();

        $respuesta = $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.store'),
            ['semana_inicio' => '2026-08-12', 'notas' => 'Semana de prueba'],
        );

        $cuadrante = CuadranteLaboral::query()->sole();

        $respuesta->assertRedirect(route('admin.planificacion-turnos.cuadrantes.show', $cuadrante));
        $this->assertSame('2026-08-10', $cuadrante->semana_inicio->toDateString());
        $this->assertSame(EstadoCuadranteLaboral::Borrador, $cuadrante->estado);
    }

    public function test_camarero_no_puede_gestionar_cuadrantes(): void
    {
        $camarero = $this->usuario(RolUsuario::Camarero);
        $this->activarModulo();

        $this->actingAs($camarero)
            ->get(route('admin.planificacion-turnos.cuadrantes.index'))
            ->assertForbidden();
    }

    public function test_modulo_inactivo_bloquea_al_encargado(): void
    {
        $encargado = $this->usuario(RolUsuario::Encargado);
        $this->activarModulo(activo: false);

        $this->actingAs($encargado)
            ->get(route('admin.planificacion-turnos.cuadrantes.index'))
            ->assertForbidden();
    }

    public function test_puede_registrar_turno_partido_en_dos_tramos(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            $this->datosJornada($empleado, $area, '08:00', '12:00'),
        )->assertRedirect();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            $this->datosJornada($empleado, $area, '18:00', '22:00'),
        )->assertRedirect();

        $this->assertCount(2, $cuadrante->jornadas()->get());
        $this->assertSame(480, $cuadrante->jornadas()->get()->sum(
            fn (JornadaLaboral $jornada): int => $jornada->minutosEfectivos(),
        ));
    }

    public function test_no_permite_solapar_jornadas_del_mismo_empleado(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            $this->datosJornada($empleado, $area, '08:00', '16:00'),
        );

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            $this->datosJornada($empleado, $area, '15:00', '22:00'),
        )->assertSessionHasErrors('hora_inicio');

        $this->assertSame(1, JornadaLaboral::query()->count());
    }

    public function test_admite_jornada_que_termina_al_dia_siguiente(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            [
                ...$this->datosJornada($empleado, $area, '20:00', '02:00'),
                'termina_dia_siguiente' => true,
                'minutos_descanso' => 30,
            ],
        )->assertRedirect();

        $jornada = JornadaLaboral::query()->sole();

        $this->assertTrue($jornada->termina_dia_siguiente);
        $this->assertSame(330, $jornada->minutosEfectivos());
    }

    public function test_detecta_solapamiento_con_jornada_nocturna_del_dia_anterior(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            [
                ...$this->datosJornada($empleado, $area, '22:00', '02:00'),
                'termina_dia_siguiente' => true,
            ],
        )->assertRedirect();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            [
                ...$this->datosJornada($empleado, $area, '01:00', '04:00'),
                'fecha' => '2026-08-11',
            ],
        )->assertSessionHasErrors('hora_inicio');

        $this->assertSame(1, JornadaLaboral::query()->count());
    }

    public function test_publicar_bloquea_nuevos_cambios_hasta_reabrir(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            $this->datosJornada($empleado, $area, '08:00', '16:00'),
        );

        $this->actingAs($encargado)
            ->patch(route('admin.planificacion-turnos.cuadrantes.publicar', $cuadrante))
            ->assertRedirect();

        $this->assertSame(EstadoCuadranteLaboral::Publicado, $cuadrante->refresh()->estado);

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            $this->datosJornada($empleado, $area, '18:00', '22:00'),
        )->assertUnprocessable();
    }

    /**
     * @return array{Usuario, Usuario, AreaTrabajo, CuadranteLaboral}
     */
    private function escenarioPlanificacion(): array
    {
        $this->activarModulo();
        $this->seed(AreaTrabajoSeeder::class);

        return [
            $this->usuario(RolUsuario::Encargado),
            $this->usuario(RolUsuario::Camarero),
            AreaTrabajo::query()->orderBy('orden')->firstOrFail(),
            CuadranteLaboral::query()->create(['semana_inicio' => '2026-08-10']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function datosJornada(
        Usuario $empleado,
        AreaTrabajo $area,
        string $inicio,
        string $fin,
    ): array {
        return [
            'usuario_id' => $empleado->id,
            'area_trabajo_id' => $area->id,
            'fecha' => '2026-08-10',
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
            'termina_dia_siguiente' => false,
            'minutos_descanso' => 0,
            'notas' => null,
        ];
    }

    private function usuario(RolUsuario $rol): Usuario
    {
        return Usuario::factory()->create(['rol' => $rol, 'es_protegido' => false]);
    }

    private function activarModulo(bool $activo = true): void
    {
        Modulo::query()->updateOrCreate(
            ['clave' => 'planificacion_turnos'],
            [
                'nombre' => 'Planificacion de turnos',
                'descripcion' => 'Cuadrantes semanales.',
                'grupo' => 'personal',
                'activo' => $activo,
                'orden' => 57,
            ],
        );
    }
}
