<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\ExportacionCuadranteLaboral;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Publica un cuadrante y crea su Excel como una única operación de dominio.
 */
final readonly class PublicarCuadranteLaboralAction
{
    public function __construct(
        private GenerarExcelCuadranteLaboralAction $generarExcel,
    ) {}

    /**
     * Si falla la base de datos se elimina el archivo escrito, evitando que
     * quede una exportación huérfana sin versión registrada.
     */
    public function ejecutar(CuadranteLaboral $cuadrante, Usuario $usuario): ExportacionCuadranteLaboral
    {
        $archivo = null;

        try {
            return DB::transaction(function () use ($cuadrante, $usuario, &$archivo): ExportacionCuadranteLaboral {
                $cuadranteBloqueado = CuadranteLaboral::query()
                    ->lockForUpdate()
                    ->findOrFail($cuadrante->getKey());
                $version = ((int) $cuadranteBloqueado->exportaciones()->max('version')) + 1;
                $publicadoAt = now();

                $cuadranteBloqueado->update([
                    'estado' => EstadoCuadranteLaboral::Publicado,
                    'publicado_at' => $publicadoAt,
                    'publicado_por_id' => $usuario->id,
                ]);

                $archivo = $this->generarExcel->ejecutar(
                    $cuadranteBloqueado,
                    $version,
                    $usuario,
                    Carbon::instance($publicadoAt),
                );

                return $cuadranteBloqueado->exportaciones()->create([
                    ...$archivo,
                    'version' => $version,
                    'generado_por_id' => $usuario->id,
                    'generado_at' => $publicadoAt,
                ]);
            }, 3);
        } catch (Throwable $exception) {
            if (is_array($archivo) && isset($archivo['disk'], $archivo['ruta'])) {
                Storage::disk($archivo['disk'])->delete($archivo['ruta']);
            }

            throw $exception;
        }
    }
}
