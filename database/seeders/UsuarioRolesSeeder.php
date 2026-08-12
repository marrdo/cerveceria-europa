<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioRolesSeeder extends Seeder
{
    /**
     * Crea usuarios operativos de prueba para validar permisos por rol.
     *
     * El superadmin tecnico lo mantiene `UsuarioAdministradorSeeder`; aqui se
     * crean perfiles reales del bar para demo y pruebas manuales.
     */
    public function run(): void
    {
        foreach ($this->usuarios() as $usuario) {
            $emailLocal = $usuario['email'];
            $email = app()->isProduction()
                ? str_replace('@demo.local', '@demo.invalid', $emailLocal)
                : $emailLocal;

            $modelo = Usuario::withTrashed()
                ->whereIn('email', [$emailLocal, $email])
                ->first() ?? new Usuario(['email' => $email]);

            $modelo->forceFill([
                'nombre' => $usuario['nombre'],
                'email' => $email,
                'rol' => $usuario['rol'],
                'es_protegido' => false,
                'password' => Hash::make(
                    app()->isProduction() ? Str::random(64) : $usuario['password'],
                ),
                'email_verified_at' => now(),
                'remember_token' => null,
                'deleted_at' => null,
            ])->save();
        }
    }

    /**
     * @return array<int, array{nombre: string, email: string, rol: RolUsuario, password: string}>
     */
    private function usuarios(): array
    {
        return [
            [
                'nombre' => 'Camarero Demo',
                'email' => 'camarero@demo.local',
                'rol' => RolUsuario::Camarero,
                'password' => 'password',
            ],
            [
                'nombre' => 'Encargado Demo',
                'email' => 'encargado@demo.local',
                'rol' => RolUsuario::Encargado,
                'password' => 'password',
            ],
            [
                'nombre' => 'Propietario Demo',
                'email' => 'propietario@demo.local',
                'rol' => RolUsuario::Propietario,
                'password' => 'password',
            ],
        ];
    }
}
