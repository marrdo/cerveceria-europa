<?php

namespace App\Modulos\PlanificacionTurnos\Actions;

use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use Illuminate\Support\Facades\DB;

class CrearJornadasEnBloqueAction
{
    public function __construct(private readonly CrearJornadaLaboralAction $crearJornada) {}

    /**
     * @param  array<int, string>  $usuarios
     * @param  array<int, string>  $fechas
     * @param  array<string, mixed>  $datosComunes
     */
    public function ejecutar(CuadranteLaboral $cuadrante, array $usuarios, array $fechas, array $datosComunes): int
    {
        return DB::transaction(function () use ($cuadrante, $datosComunes, $fechas, $usuarios): int {
            $creadas = 0;

            foreach ($usuarios as $usuarioId) {
                foreach ($fechas as $fecha) {
                    $this->crearJornada->ejecutar($cuadrante, [
                        ...$datosComunes,
                        'usuario_id' => $usuarioId,
                        'fecha' => $fecha,
                    ]);
                    $creadas++;
                }
            }

            return $creadas;
        });
    }
}
