<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use Illuminate\Database\Seeder;
use Carbon\CarbonImmutable;

/**
 * Deja un cuadrante ficticio visible al entrar en la demo.
 */
class PlanificacionTurnosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $lunes = CarbonImmutable::now('Europe/Madrid')->startOfWeek();
        $cuadrante = CuadranteLaboral::query()->updateOrCreate(
            ['semana_inicio' => $lunes->toDateString()],
            [
                'estado' => EstadoCuadranteLaboral::Borrador,
                'notas' => 'Cuadrante ficticio preparado para probar la planificación semanal.',
                'publicado_at' => null,
                'publicado_por_id' => null,
            ],
        );

        $cuadrante->jornadas()->delete();

        $usuarios = Usuario::query()
            ->whereIn('email', ['camarero@demo.local', 'encargado@demo.local', 'propietario@demo.local'])
            ->get()
            ->keyBy('email');
        $areas = AreaTrabajo::query()->get()->keyBy('nombre');

        foreach ($this->jornadas() as $jornada) {
            $cuadrante->jornadas()->create([
                'usuario_id' => $usuarios[$jornada['email']]->id,
                'area_trabajo_id' => $areas[$jornada['area']]->id,
                'fecha' => $lunes->addDays($jornada['dia'])->toDateString(),
                'hora_inicio' => $jornada['inicio'],
                'hora_fin' => $jornada['fin'],
                'termina_dia_siguiente' => false,
                'minutos_descanso' => $jornada['pausa'],
                'notas' => 'Jornada de demostración.',
            ]);
        }
    }

    /** @return array<int, array{email: string, area: string, dia: int, inicio: string, fin: string, pausa: int}> */
    private function jornadas(): array
    {
        return [
            ['email' => 'camarero@demo.local', 'area' => 'Sala y barra', 'dia' => 0, 'inicio' => '08:00', 'fin' => '12:00', 'pausa' => 0],
            ['email' => 'camarero@demo.local', 'area' => 'Sala y barra', 'dia' => 0, 'inicio' => '18:00', 'fin' => '22:00', 'pausa' => 0],
            ['email' => 'encargado@demo.local', 'area' => 'Sala y barra', 'dia' => 0, 'inicio' => '09:00', 'fin' => '17:00', 'pausa' => 30],
            ['email' => 'propietario@demo.local', 'area' => 'Trastienda', 'dia' => 1, 'inicio' => '08:00', 'fin' => '15:00', 'pausa' => 0],
            ['email' => 'camarero@demo.local', 'area' => 'Sala y barra', 'dia' => 2, 'inicio' => '10:00', 'fin' => '18:00', 'pausa' => 30],
            ['email' => 'encargado@demo.local', 'area' => 'Sala y barra', 'dia' => 3, 'inicio' => '14:00', 'fin' => '22:00', 'pausa' => 30],
            ['email' => 'propietario@demo.local', 'area' => 'Trastienda', 'dia' => 4, 'inicio' => '08:00', 'fin' => '16:00', 'pausa' => 30],
        ];
    }
}
