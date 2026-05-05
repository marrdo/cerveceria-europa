<?php

namespace App\Http\Requests\Admin;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Support\Validacion\ReglasValidacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ActualizarUsuarioPersonalRequest extends FormRequest
{
    /**
     * Autoriza la edicion del usuario indicado si pertenece a un rol gestionable.
     */
    public function authorize(): bool
    {
        $usuario = $this->route('usuario');

        return $usuario instanceof Usuario
            && $this->user()?->puedeGestionarUsuario($usuario) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Usuario $usuario */
        $usuario = $this->route('usuario');

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => [...ReglasValidacion::email(nullable: false), Rule::unique(Usuario::class, 'email')->ignore($usuario->id)],
            'rol' => ['required', 'string', Rule::in($this->rolesPermitidos())],
            'password' => ['nullable', 'confirmed', Password::defaults()],
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
            'rol.in' => 'No puedes asignar ese rol.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
        ];
    }

    /**
     * Datos normalizados para actualizar el usuario.
     *
     * @return array<string, mixed>
     */
    public function datosUsuario(): array
    {
        $datos = [
            'nombre' => trim((string) $this->input('nombre')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'rol' => RolUsuario::from((string) $this->input('rol')),
        ];

        if ($this->filled('password')) {
            $datos['password'] = $this->input('password');
        }

        return $datos;
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
