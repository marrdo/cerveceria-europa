<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Completa el equipo ficticio de la demo hasta 22 personas operativas.
 *
 * Los tres perfiles reconocibles para iniciar sesión se crean en
 * {@see UsuarioRolesSeeder}. Este seeder añade 19 compañeros generados por la
 * factoría, sin reutilizar nombres, correos ni otros datos del Excel original.
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
        foreach (range(1, self::TOTAL_PERSONAL_GENERADO) as $indice) {
            $email = sprintf('equipo%02d@demo.local', $indice);
            $usuarioFicticio = Usuario::factory()->make([
                'email' => $email,
                'rol' => $indice === 1 ? RolUsuario::Encargado : RolUsuario::Camarero,
                'es_protegido' => false,
            ]);

            $usuario = Usuario::withTrashed()->firstOrNew(['email' => $email]);
            $usuario->forceFill($usuarioFicticio->getAttributes());
            $usuario->deleted_at = null;
            $usuario->save();
        }
    }
}
