<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdministradorSeeder extends Seeder
{
    /**
     * Crea el usuario tecnico inicial del panel.
     *
     * Las credenciales se leen de variables de entorno para poder reutilizar
     * esta base en otros proyectos sin tocar codigo.
     */
    public function run(): void
    {
        Usuario::query()->updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'admin@cerveceria-europa.local')],
            [
                'nombre' => env('SUPERADMIN_NAME', 'Superadmin Cerveceria Europa'),
                'rol' => RolUsuario::Superadmin,
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ],
        );
    }
}
