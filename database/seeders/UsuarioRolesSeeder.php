<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            Usuario::query()->updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['rol'],
                    'es_protegido' => false,
                    'password' => Hash::make($usuario['password']),
                    'email_verified_at' => now(),
                ],
            );
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
                'email' => 'camarero@cerveceria-europa.local',
                'rol' => RolUsuario::Camarero,
                'password' => 'password',
            ],
            [
                'nombre' => 'Encargado Demo',
                'email' => 'encargado@cerveceria-europa.local',
                'rol' => RolUsuario::Encargado,
                'password' => 'password',
            ],
            [
                'nombre' => 'Propietario Demo',
                'email' => 'propietario@cerveceria-europa.local',
                'rol' => RolUsuario::Propietario,
                'password' => 'password',
            ],
        ];
    }
}
