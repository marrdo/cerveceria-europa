<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Completa el equipo ficticio de la demo hasta 22 personas operativas.
 *
 * Los tres perfiles reconocibles para iniciar sesión se crean en
 * {@see UsuarioRolesSeeder}. Este seeder añade 19 compañeros deterministas sin
 * depender de Faker, que es una herramienta de desarrollo ausente en producción.
 */
class PersonalDemoSeeder extends Seeder
{
    public const TOTAL_PERSONAL_OPERATIVO = 22;

    public const TOTAL_PERSONAL_GENERADO = 19;

    /**
     * Genera personal ficticio manteniendo correos estables para que el seeder
     * pueda ejecutarse varias veces sin duplicar cuentas.
     */
    public function run(): void
    {
        $nombres = [
            'Adriana Campos',
            'Álvaro Benítez',
            'Beatriz Romero',
            'Bruno Márquez',
            'Carla Navarro',
            'Daniel Ortega',
            'Elena Prieto',
            'Hugo Santana',
            'Irene Lozano',
            'Javier Cabrera',
            'Laura Medina',
            'Marcos Vega',
            'Marta Fuentes',
            'Nicolás Castro',
            'Noelia Reyes',
            'Pablo Serrano',
            'Raquel Molina',
            'Sergio Gil',
            'Sofía León',
        ];

        foreach (range(1, self::TOTAL_PERSONAL_GENERADO) as $indice) {
            $emailLocal = sprintf('equipo%02d@demo.local', $indice);
            $email = app()->isProduction()
                ? str_replace('@demo.local', '@demo.invalid', $emailLocal)
                : $emailLocal;

            $usuario = Usuario::withTrashed()
                ->whereIn('email', [$emailLocal, $email])
                ->first() ?? new Usuario(['email' => $email]);
            $usuario->forceFill([
                'nombre' => $nombres[$indice - 1],
                'email' => $email,
                'rol' => $indice === 1 ? RolUsuario::Encargado : RolUsuario::Camarero,
                'es_protegido' => false,
                'minutos_contrato_semanales' => 2400,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(64)),
                'remember_token' => null,
            ]);
            $usuario->deleted_at = null;
            $usuario->save();
        }
    }
}
