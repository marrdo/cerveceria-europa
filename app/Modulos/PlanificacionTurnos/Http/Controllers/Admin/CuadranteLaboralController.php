<?php

namespace App\Modulos\PlanificacionTurnos\Http\Controllers\Admin;

use App\Enums\RolUsuario;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Actions\AplicarPlantillaCuadranteAction;
use App\Modulos\PlanificacionTurnos\Actions\CalcularAlertasCoberturaAction;
use App\Modulos\PlanificacionTurnos\Actions\CopiarCuadranteLaboralAction;
use App\Modulos\PlanificacionTurnos\Actions\CrearIncidenciaLaboralAction;
use App\Modulos\PlanificacionTurnos\Actions\CrearJornadaLaboralAction;
use App\Modulos\PlanificacionTurnos\Actions\CrearJornadasEnBloqueAction;
use App\Modulos\PlanificacionTurnos\Actions\DetectarConflictosLaboralesAction;
use App\Modulos\PlanificacionTurnos\Actions\GuardarPlantillaCuadranteAction;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Enums\TipoIncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Http\Requests\CrearSemanaPlanificadaRequest;
use App\Modulos\PlanificacionTurnos\Http\Requests\GuardarCoberturaMinimaRequest;
use App\Modulos\PlanificacionTurnos\Http\Requests\GuardarCuadranteLaboralRequest;
use App\Modulos\PlanificacionTurnos\Http\Requests\GuardarIncidenciaLaboralRequest;
use App\Modulos\PlanificacionTurnos\Http\Requests\GuardarJornadaLaboralRequest;
use App\Modulos\PlanificacionTurnos\Http\Requests\GuardarJornadasEnBloqueRequest;
use App\Modulos\PlanificacionTurnos\Http\Requests\GuardarPlantillaCuadranteRequest;
use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CoberturaMinimaLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\IncidenciaLaboral;
use App\Modulos\PlanificacionTurnos\Models\JornadaLaboral;
use App\Modulos\PlanificacionTurnos\Models\PlantillaCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\ViewData\CuadranteSemanalViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Gestiona los cuadrantes semanales y sus tramos de trabajo.
 */
class CuadranteLaboralController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);

        return view('modulos.planificacion-turnos.cuadrantes.index', [
            'cuadrantes' => CuadranteLaboral::query()
                ->withCount('jornadas')
                ->orderByDesc('semana_inicio')
                ->paginate(12),
            'proximoLunes' => now()->startOfWeek()->toDateString(),
            'plantillas' => PlantillaCuadranteLaboral::query()->withCount('jornadas')->orderBy('nombre')->get(),
        ]);
    }

    public function store(GuardarCuadranteLaboralRequest $request): RedirectResponse
    {
        $cuadrante = CuadranteLaboral::query()->create($request->datosCuadrante());

        return redirect()->route('admin.planificacion-turnos.cuadrantes.show', $cuadrante)
            ->with('status', 'Cuadrante semanal creado correctamente.');
    }

    public function show(
        Request $request,
        CuadranteLaboral $cuadrante,
        CuadranteSemanalViewData $viewData,
        DetectarConflictosLaboralesAction $detectarConflictos,
        CalcularAlertasCoberturaAction $calcularAlertasCobertura,
    ): View {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);

        $cuadrante->load([
            'jornadas' => fn ($query) => $query
                ->with(['usuario', 'areaTrabajo'])
                ->orderBy('fecha')
                ->orderBy('hora_inicio'),
        ]);

        $dias = $this->diasSemana($cuadrante);
        $empleados = Usuario::query()
            ->where('rol', '!=', RolUsuario::Superadmin->value)
            ->where('es_protegido', false)
            ->orderBy('nombre')
            ->get();
        $incidencias = IncidenciaLaboral::query()
            ->with('usuario')
            ->coincideConPeriodo($cuadrante->semana_inicio, $cuadrante->semanaFin())
            ->orderBy('fecha_inicio')
            ->get();
        $reglasCobertura = CoberturaMinimaLaboral::query()
            ->with('areaTrabajo')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return view('modulos.planificacion-turnos.cuadrantes.show', [
            'cuadrante' => $cuadrante,
            'dias' => $dias,
            'empleados' => $empleados,
            'areas' => AreaTrabajo::query()->where('activo', true)->orderBy('orden')->orderBy('nombre')->get(),
            'filasPlanificacion' => $viewData->construir($cuadrante, $empleados, $dias, $incidencias),
            'festivosPorDia' => $dias->mapWithKeys(fn (Carbon $dia): array => [
                $dia->toDateString() => $incidencias
                    ->filter(fn (IncidenciaLaboral $incidencia): bool => $incidencia->esGlobal() && $incidencia->afectaFecha($dia))
                    ->values(),
            ]),
            'tiposIncidencia' => TipoIncidenciaLaboral::cases(),
            'conflictosLaborales' => $detectarConflictos->ejecutar($cuadrante),
            'reglasCobertura' => $reglasCobertura,
            'alertasCobertura' => $calcularAlertasCobertura->ejecutar($cuadrante, $reglasCobertura),
            'proximaSemana' => $cuadrante->semanaFin()->addDay()->toDateString(),
        ]);
    }

    public function storeJornada(
        GuardarJornadaLaboralRequest $request,
        CuadranteLaboral $cuadrante,
        CrearJornadaLaboralAction $crearJornada,
    ): RedirectResponse {
        abort_unless($cuadrante->esBorrador(), 422, 'No puedes modificar un cuadrante publicado.');

        $crearJornada->ejecutar($cuadrante, $request->datosJornada());

        return back()->with('status', 'Tramo de trabajo añadido correctamente.');
    }

    public function destroyJornada(
        Request $request,
        CuadranteLaboral $cuadrante,
        JornadaLaboral $jornada,
    ): RedirectResponse {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);
        abort_unless($cuadrante->esBorrador(), 422, 'No puedes modificar un cuadrante publicado.');
        abort_unless($jornada->cuadrante_laboral_id === $cuadrante->id, 404);

        $jornada->delete();

        return back()->with('status', 'Tramo eliminado correctamente.');
    }

    public function storeJornadasBloque(
        GuardarJornadasEnBloqueRequest $request,
        CuadranteLaboral $cuadrante,
        CrearJornadasEnBloqueAction $crearJornadas,
    ): RedirectResponse {
        abort_unless($cuadrante->esBorrador(), 422, 'No puedes modificar un cuadrante publicado.');

        $creadas = $crearJornadas->ejecutar(
            $cuadrante,
            $request->input('usuario_ids'),
            $request->input('fechas'),
            $request->datosComunes(),
        );

        return back()->with('status', "Se han creado {$creadas} turnos en bloque.");
    }

    public function copiar(
        CrearSemanaPlanificadaRequest $request,
        CuadranteLaboral $cuadrante,
        CopiarCuadranteLaboralAction $copiarCuadrante,
    ): RedirectResponse {
        $copia = $copiarCuadrante->ejecutar($cuadrante, $request->semanaInicio());

        return redirect()->route('admin.planificacion-turnos.cuadrantes.show', $copia)
            ->with('status', 'Semana copiada correctamente como borrador.');
    }

    public function storePlantilla(
        GuardarPlantillaCuadranteRequest $request,
        CuadranteLaboral $cuadrante,
        GuardarPlantillaCuadranteAction $guardarPlantilla,
    ): RedirectResponse {
        $guardarPlantilla->ejecutar($cuadrante, $request->datosPlantilla());

        return back()->with('status', 'Plantilla semanal guardada correctamente.');
    }

    public function aplicarPlantilla(
        CrearSemanaPlanificadaRequest $request,
        PlantillaCuadranteLaboral $plantilla,
        AplicarPlantillaCuadranteAction $aplicarPlantilla,
    ): RedirectResponse {
        $cuadrante = $aplicarPlantilla->ejecutar($plantilla, $request->semanaInicio());

        return redirect()->route('admin.planificacion-turnos.cuadrantes.show', $cuadrante)
            ->with('status', 'Cuadrante creado desde la plantilla.');
    }

    public function destroyPlantilla(Request $request, PlantillaCuadranteLaboral $plantilla): RedirectResponse
    {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);
        $plantilla->delete();

        return back()->with('status', 'Plantilla eliminada correctamente.');
    }

    public function storeCobertura(GuardarCoberturaMinimaRequest $request): RedirectResponse
    {
        CoberturaMinimaLaboral::query()->create($request->datosCobertura());

        return back()->with('status', 'Regla de cobertura creada correctamente.');
    }

    public function destroyCobertura(Request $request, CoberturaMinimaLaboral $cobertura): RedirectResponse
    {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);
        $cobertura->delete();

        return back()->with('status', 'Regla de cobertura eliminada correctamente.');
    }

    public function storeIncidencia(
        GuardarIncidenciaLaboralRequest $request,
        CuadranteLaboral $cuadrante,
        CrearIncidenciaLaboralAction $crearIncidencia,
        DetectarConflictosLaboralesAction $detectarConflictos,
    ): RedirectResponse {
        $crearIncidencia->ejecutar($request->datosIncidencia());
        $conflictos = $detectarConflictos->ejecutar($cuadrante)->count();

        return back()->with(
            'status',
            $conflictos > 0
                ? "Incidencia registrada. Hay {$conflictos} turnos incompatibles que debes revisar."
                : 'Incidencia registrada correctamente.',
        );
    }

    public function destroyIncidencia(
        Request $request,
        CuadranteLaboral $cuadrante,
        IncidenciaLaboral $incidencia,
    ): RedirectResponse {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);
        abort_unless(
            $incidencia->fecha_inicio->lte($cuadrante->semanaFin())
            && $incidencia->fecha_fin->gte($cuadrante->semana_inicio),
            404,
        );

        $incidencia->delete();

        return back()->with('status', 'Incidencia eliminada correctamente.');
    }

    public function publicar(
        Request $request,
        CuadranteLaboral $cuadrante,
        DetectarConflictosLaboralesAction $detectarConflictos,
    ): RedirectResponse {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);
        abort_if($cuadrante->jornadas()->doesntExist(), 422, 'Anade al menos una jornada antes de publicar.');

        $conflictos = $detectarConflictos->ejecutar($cuadrante)->count();

        if ($conflictos > 0) {
            throw ValidationException::withMessages([
                'cuadrante' => "No puedes publicar: hay {$conflictos} turnos asignados durante descansos, vacaciones, bajas o ausencias.",
            ]);
        }

        $cuadrante->update([
            'estado' => EstadoCuadranteLaboral::Publicado,
            'publicado_at' => now(),
            'publicado_por_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Cuadrante publicado correctamente.');
    }

    public function reabrir(Request $request, CuadranteLaboral $cuadrante): RedirectResponse
    {
        abort_unless($request->user()?->puedeGestionarPlanificacionTurnos(), 403);

        $cuadrante->update([
            'estado' => EstadoCuadranteLaboral::Borrador,
            'publicado_at' => null,
            'publicado_por_id' => null,
        ]);

        return back()->with('status', 'Cuadrante reabierto como borrador.');
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function diasSemana(CuadranteLaboral $cuadrante): Collection
    {
        return collect(range(0, 6))->map(
            fn (int $desplazamiento) => $cuadrante->semana_inicio->copy()->addDays($desplazamiento),
        );
    }
}
