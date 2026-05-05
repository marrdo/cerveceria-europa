<?php

namespace App\Http\Requests\Admin;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Support\Validacion\ReglasValidacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class GuardarUsuarioPersonalRequest extends FormRequest
{
    /**
     * Autoriza el alta de personal segun los roles que puede gestionar el usuario autenticado.
     */
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPersonal() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => [...ReglasValidacion::email(nullable: false), Rule::unique(Usuario::class, 'email')],
            'rol' => ['required', 'string', Rule::in($this->rolesPermitidos())],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Ya existe un usuario con este email.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'No puedes crear usuarios con ese rol.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
        ];
    }

    /**
     * Datos normalizados para crear el usuario.
     *
     * @return array<string, mixed>
     */
    public function datosUsuario(): array
    {
        return [
            'nombre' => trim((string) $this->input('nombre')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'rol' => RolUsuario::from((string) $this->input('rol')),
            'password' => $this->input('password'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function rolesPermitidos(): array
    {
        return $this->user()
            ? $this->user()->rolesGestionables()->map(fn (RolUsuario $rol): string => $rol->value)->all()
            : [];
    }
}
