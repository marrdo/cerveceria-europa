<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Enums\TipoIncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CoberturaMinimaLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\ExportacionCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\JornadaLaboral;
use App\Modulos\PlanificacionTurnos\Models\PlantillaCuadranteLaboral;
use Database\Seeders\AreaTrabajoSeeder;
use Database\Seeders\ModuloSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class PlanificacionTurnosModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

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

        $exportacion = ExportacionCuadranteLaboral::query()->sole();
        $this->assertSame(1, $exportacion->version);
        $this->assertSame($encargado->id, $exportacion->generado_por_id);
        $this->assertSame(64, strlen($exportacion->hash_sha256));
        $this->assertGreaterThan(0, $exportacion->tamano_bytes);
        Storage::disk($exportacion->disk)->assertExists($exportacion->ruta);

        $libro = IOFactory::load(Storage::disk($exportacion->disk)->path($exportacion->ruta));
        $hoja = $libro->getSheetByName('Cuadrante');
        $this->assertNotNull($hoja);
        $this->assertStringContainsString('VERSIÓN 001', (string) $hoja->getCell('A2')->getValue());
        $valoresPrimeraColumna = collect(
            $hoja->rangeToArray('A1:A'.$hoja->getHighestRow(), null, true, true, false),
        )->flatten()->implode('|');
        $this->assertStringContainsString($empleado->nombre, $valoresPrimeraColumna);
        $libro->disconnectWorksheets();

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
            $this->datosJornada($empleado, $area, '18:00', '22:00'),
        )->assertUnprocessable();
    }

    public function test_reabrir_y_republicar_conserva_el_excel_anterior_y_crea_otra_version(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '08:00', '16:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
        ]);

        $this->actingAs($encargado)
            ->patch(route('admin.planificacion-turnos.cuadrantes.publicar', $cuadrante))
            ->assertRedirect();
        $primera = ExportacionCuadranteLaboral::query()->sole();

        $this->actingAs($encargado)
            ->patch(route('admin.planificacion-turnos.cuadrantes.reabrir', $cuadrante))
            ->assertRedirect();
        $this->assertSame(EstadoCuadranteLaboral::Borrador, $cuadrante->refresh()->estado);
        Storage::disk($primera->disk)->assertExists($primera->ruta);

        $this->actingAs($encargado)
            ->patch(route('admin.planificacion-turnos.cuadrantes.publicar', $cuadrante))
            ->assertRedirect();

        $versiones = $cuadrante->exportaciones()->orderBy('version')->get();
        $this->assertSame([1, 2], $versiones->pluck('version')->all());
        $this->assertNotSame($versiones[0]->ruta, $versiones[1]->ruta);
        Storage::disk($versiones[0]->disk)->assertExists($versiones[0]->ruta);
        Storage::disk($versiones[1]->disk)->assertExists($versiones[1]->ruta);
    }

    public function test_encargado_puede_descargar_una_version_excel_privada(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '08:00', '16:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
        ]);

        $this->actingAs($encargado)
            ->patch(route('admin.planificacion-turnos.cuadrantes.publicar', $cuadrante))
            ->assertRedirect();
        $exportacion = ExportacionCuadranteLaboral::query()->sole();

        $this->actingAs($encargado)
            ->get(route('admin.planificacion-turnos.cuadrantes.exportaciones.descargar', [$cuadrante, $exportacion]))
            ->assertOk()
            ->assertDownload($exportacion->nombre_archivo);
    }

    public function test_puede_registrar_vacaciones_que_atraviesan_varios_dias(): void
    {
        [$encargado, $empleado, , $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)
            ->post(
                route('admin.planificacion-turnos.cuadrantes.incidencias.store', $cuadrante),
                $this->datosIncidencia($empleado, TipoIncidenciaLaboral::Vacaciones, '2026-08-10', '2026-08-14'),
            )
            ->assertRedirect();

        $incidencia = IncidenciaLaboral::query()->sole();

        $this->assertSame($empleado->id, $incidencia->usuario_id);
        $this->assertSame(TipoIncidenciaLaboral::Vacaciones, $incidencia->tipo);
        $this->assertSame('2026-08-10', $incidencia->fecha_inicio->toDateString());
        $this->assertSame('2026-08-14', $incidencia->fecha_fin->toDateString());
        $this->assertSame($encargado->id, $incidencia->creado_por_id);
    }

    public function test_festivo_se_registra_sin_empleado_y_se_muestra_en_el_calendario(): void
    {
        [$encargado, , , $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)
            ->post(
                route('admin.planificacion-turnos.cuadrantes.incidencias.store', $cuadrante),
                [
                    'tipo' => TipoIncidenciaLaboral::Festivo->value,
                    'usuario_id' => null,
                    'fecha_inicio' => '2026-08-15',
                    'fecha_fin' => '2026-08-15',
                    'notas' => 'Festivo local',
                ],
            )
            ->assertRedirect();

        $this->assertDatabaseHas('incidencias_laborales', [
            'usuario_id' => null,
            'tipo' => TipoIncidenciaLaboral::Festivo->value,
            'notas' => 'Festivo local',
        ]);

        $this->actingAs($encargado)
            ->get(route('admin.planificacion-turnos.cuadrantes.show', $cuadrante))
            ->assertOk()
            ->assertSee('Festivo local');
    }

    public function test_no_permite_incidencias_personales_solapadas(): void
    {
        [$encargado, $empleado, , $cuadrante] = $this->escenarioPlanificacion();

        IncidenciaLaboral::query()->create([
            ...$this->datosIncidencia($empleado, TipoIncidenciaLaboral::Vacaciones, '2026-08-10', '2026-08-12'),
            'creado_por_id' => $encargado->id,
        ]);

        $this->actingAs($encargado)
            ->post(
                route('admin.planificacion-turnos.cuadrantes.incidencias.store', $cuadrante),
                $this->datosIncidencia($empleado, TipoIncidenciaLaboral::Baja, '2026-08-12', '2026-08-14'),
            )
            ->assertSessionHasErrors('fecha_inicio');

        $this->assertSame(1, IncidenciaLaboral::query()->count());
    }

    public function test_no_permite_asignar_turno_durante_una_incidencia(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();

        IncidenciaLaboral::query()->create([
            ...$this->datosIncidencia($empleado, TipoIncidenciaLaboral::Descanso, '2026-08-10', '2026-08-10'),
            'creado_por_id' => $encargado->id,
        ]);

        $this->actingAs($encargado)
            ->post(
                route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante),
                $this->datosJornada($empleado, $area, '08:00', '16:00'),
            )
            ->assertSessionHasErrors('fecha');

        $this->assertSame(0, JornadaLaboral::query()->count());
    }

    public function test_no_permite_publicar_si_un_turno_coincide_con_una_baja(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();

        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '08:00', '16:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
        ]);
        IncidenciaLaboral::query()->create([
            ...$this->datosIncidencia($empleado, TipoIncidenciaLaboral::Baja, '2026-08-10', '2026-08-10'),
            'creado_por_id' => $encargado->id,
        ]);

        $this->actingAs($encargado)
            ->patch(route('admin.planificacion-turnos.cuadrantes.publicar', $cuadrante))
            ->assertSessionHasErrors('cuadrante');

        $this->assertSame(EstadoCuadranteLaboral::Borrador, $cuadrante->refresh()->estado);
    }

    public function test_vista_semanal_organiza_todo_el_personal_por_filas_y_dias(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        $empleadoSinTurno = Usuario::factory()->create([
            'nombre' => 'Persona sin asignar',
            'rol' => RolUsuario::Camarero,
            'es_protegido' => false,
        ]);

        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '08:00', '16:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
        ]);

        $this->actingAs($encargado)
            ->get(route('admin.planificacion-turnos.cuadrantes.show', $cuadrante))
            ->assertOk()
            ->assertSee('Vista por empleado')
            ->assertSee('Buscar empleado')
            ->assertSee($empleado->nombre)
            ->assertSee($empleadoSinTurno->nombre)
            ->assertSee('Compacta')
            ->assertSee('Detallada')
            ->assertSee('8,0 h');
    }

    public function test_puede_copiar_un_cuadrante_a_otra_semana(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '08:00', '16:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
        ]);

        $respuesta = $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.copiar', $cuadrante),
            ['semana_inicio' => '2026-08-17'],
        );

        $copia = CuadranteLaboral::query()->whereDate('semana_inicio', '2026-08-17')->sole();
        $respuesta->assertRedirect(route('admin.planificacion-turnos.cuadrantes.show', $copia));
        $jornadaCopiada = $copia->jornadas()->sole();
        $this->assertSame($empleado->id, $jornadaCopiada->usuario_id);
        $this->assertSame('2026-08-17', $jornadaCopiada->fecha->toDateString());
    }

    public function test_puede_guardar_y_aplicar_una_plantilla_semanal(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '10:00', '18:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
        ]);

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.plantillas.store', $cuadrante),
            ['nombre' => 'Semana de prueba'],
        )->assertRedirect();

        $plantilla = PlantillaCuadranteLaboral::query()->sole();
        $this->assertSame(1, $plantilla->jornadas()->count());

        $respuesta = $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.plantillas.aplicar', $plantilla),
            ['semana_inicio' => '2026-08-24'],
        );

        $creado = CuadranteLaboral::query()->whereDate('semana_inicio', '2026-08-24')->sole();
        $respuesta->assertRedirect(route('admin.planificacion-turnos.cuadrantes.show', $creado));
        $this->assertSame(1, $creado->jornadas()->count());
    }

    public function test_puede_crear_turnos_para_varios_empleados_y_dias_en_bloque(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        $segundoEmpleado = $this->usuario(RolUsuario::Camarero);

        $this->actingAs($encargado)->post(
            route('admin.planificacion-turnos.cuadrantes.jornadas.bloque', $cuadrante),
            [
                'usuario_ids' => [$empleado->id, $segundoEmpleado->id],
                'fechas' => ['2026-08-10', '2026-08-11'],
                'area_trabajo_id' => $area->id,
                'hora_inicio' => '08:00',
                'hora_fin' => '16:00',
                'minutos_descanso' => 30,
            ],
        )->assertRedirect();

        $this->assertSame(4, $cuadrante->jornadas()->count());
    }

    public function test_muestra_desviacion_de_contrato_y_alertas_de_cobertura(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        $empleado->update(['minutos_contrato_semanales' => 1200]);
        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '08:00', '16:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
        ]);
        CoberturaMinimaLaboral::query()->create([
            'area_trabajo_id' => $area->id,
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fin' => '10:00',
            'minimo_personas' => 2,
            'activo' => true,
        ]);

        $this->actingAs($encargado)
            ->get(route('admin.planificacion-turnos.cuadrantes.show', $cuadrante))
            ->assertOk()
            ->assertSee('avisos de cobertura')
            ->assertSee('1 de 2 personas')
            ->assertSee('de 20,0 h');
    }

    public function test_formularios_de_planificacion_permiten_buscar_empleados_por_nombre(): void
    {
        [$encargado, , , $cuadrante] = $this->escenarioPlanificacion();

        $this->actingAs($encargado)
            ->get(route('admin.planificacion-turnos.cuadrantes.show', $cuadrante))
            ->assertOk()
            ->assertSee('Buscar empleado para el turno...')
            ->assertSee('Buscar empleado para la incidencia...')
            ->assertSee('Buscar empleados para asignar en bloque');
    }

    public function test_camarero_consulta_solo_sus_turnos_de_semanas_publicadas(): void
    {
        [$encargado, $empleado, $area, $cuadrante] = $this->escenarioPlanificacion();
        $otroEmpleado = $this->usuario(RolUsuario::Camarero);

        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '08:00', '16:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
            'notas' => 'Preparar apertura de terraza',
        ]);
        JornadaLaboral::query()->create([
            ...$this->datosJornada($otroEmpleado, $area, '16:00', '23:00'),
            'cuadrante_laboral_id' => $cuadrante->id,
            'notas' => 'Nota privada de otra persona',
        ]);
        $cuadrante->update([
            'estado' => EstadoCuadranteLaboral::Publicado,
            'publicado_at' => now(),
            'publicado_por_id' => $encargado->id,
        ]);

        $borrador = CuadranteLaboral::query()->create(['semana_inicio' => '2026-08-17']);
        JornadaLaboral::query()->create([
            ...$this->datosJornada($empleado, $area, '09:00', '15:00'),
            'cuadrante_laboral_id' => $borrador->id,
            'fecha' => '2026-08-17',
            'notas' => 'Turno todavía sin publicar',
        ]);

        $this->actingAs($empleado)
            ->get(route('admin.mis-turnos.index'))
            ->assertOk()
            ->assertSee('Mis turnos')
            ->assertSee('Preparar apertura de terraza')
            ->assertSee('08:00–16:00')
            ->assertDontSee('Nota privada de otra persona')
            ->assertDontSee('Turno todavía sin publicar')
            ->assertDontSee('17/08/2026');
    }

    public function test_mis_turnos_desaparece_si_el_modulo_no_esta_operativo(): void
    {
        $this->activarModulo(false);
        $empleado = $this->usuario(RolUsuario::Camarero);

        $this->actingAs($empleado)
            ->get(route('admin.mis-turnos.index'))
            ->assertNotFound();
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

    /**
     * @return array<string, mixed>
     */
    private function datosIncidencia(
        Usuario $empleado,
        TipoIncidenciaLaboral $tipo,
        string $inicio,
        string $fin,
    ): array {
        return [
            'tipo' => $tipo->value,
            'usuario_id' => $empleado->id,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'notas' => null,
        ];
    }

    private function usuario(RolUsuario $rol): Usuario
    {
        return Usuario::factory()->create(['rol' => $rol, 'es_protegido' => false]);
    }

    private function activarModulo(bool $activo = true): void
    {
        $this->seed(ModuloSeeder::class);
        Modulo::query()
            ->where('clave', 'planificacion_turnos')
            ->update(['activo' => $activo]);
    }
}
