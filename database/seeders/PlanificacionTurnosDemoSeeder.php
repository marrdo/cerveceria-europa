<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use App\Modulos\PlanificacionTurnos\Models\AreaTrabajo;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Deja un cuadrante semanal completo y ficticio visible en la demo.
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

        $usuarios = $this->personalOperativo();
        $areas = AreaTrabajo::query()->get()->keyBy('nombre');

        foreach ($usuarios->values() as $indice => $usuario) {
            $area = $indice < 17 ? $areas['Sala y barra'] : $areas['Trastienda'];
            $diasDescanso = [$indice % 7, ($indice + 3) % 7];

            foreach (range(0, 6) as $dia) {
                if (in_array($dia, $diasDescanso, true)) {
                    continue;
                }

                foreach ($this->tramosDelDia($indice, $dia) as $tramo) {
                    $cuadrante->jornadas()->create([
                        'usuario_id' => $usuario->id,
                        'area_trabajo_id' => $area->id,
                        'fecha' => $lunes->addDays($dia)->toDateString(),
                        'hora_inicio' => $tramo['inicio'],
                        'hora_fin' => $tramo['fin'],
                        'termina_dia_siguiente' => false,
                        'minutos_descanso' => $tramo['pausa'],
                        'notas' => 'Jornada de demostración.',
                    ]);
                }
            }
        }
    }

    /**
     * Devuelve las 22 personas que forman el equipo operativo de la demo.
     *
     * @return Collection<int, Usuario>
     */
    private function personalOperativo(): Collection
    {
        $orden = collect([
            'encargado@demo.local',
            'propietario@demo.local',
            'camarero@demo.local',
            ...array_map(
                static fn (int $indice): string => sprintf('equipo%02d@demo.local', $indice),
                range(1, PersonalDemoSeeder::TOTAL_PERSONAL_GENERADO),
            ),
        ])->flip();

        return Usuario::query()
            ->where('rol', '!=', RolUsuario::Superadmin)
            ->whereIn('email', $orden->keys())
            ->get()
            ->sortBy(static fn (Usuario $usuario): int => $orden[$usuario->email])
            ->values();
    }

    /**
     * Alterna mañanas, tardes y turnos partidos para que la cuadrícula permita
     * evaluar de un vistazo los casos habituales de un negocio de hostelería.
     *
     * @return array<int, array{inicio: string, fin: string, pausa: int}>
     */
    private function tramosDelDia(int $indicePersona, int $dia): array
    {
        if (($indicePersona + $dia) % 9 === 0) {
            return [
                ['inicio' => '08:00', 'fin' => '12:00', 'pausa' => 0],
                ['inicio' => '18:00', 'fin' => '22:00', 'pausa' => 0],
            ];
        }

        return match (($indicePersona + $dia) % 3) {
            0 => [['inicio' => '08:00', 'fin' => '16:00', 'pausa' => 30]],
            1 => [['inicio' => '10:00', 'fin' => '18:00', 'pausa' => 30]],
            default => [['inicio' => '14:00', 'fin' => '22:00', 'pausa' => 30]],
        };
    }
}
