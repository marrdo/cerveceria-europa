<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActualizarUsuarioPersonalRequest;
use App\Http\Requests\Admin\GuardarUsuarioPersonalRequest;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PersonalController extends Controller
{
    /**
     * Lista usuarios visibles para la persona que gestiona personal.
     */
    public function index(Request $request): View
    {
        $rolesGestionables = $request->user()?->rolesGestionables() ?? collect();

        $usuarios = Usuario::query()
            ->whereIn('rol', $rolesGestionables->map(fn ($rol): string => $rol->value))
            ->where('id', '!=', $request->user()?->id)
            ->where('es_protegido', false)
            ->orderBy('rol')
            ->orderBy('nombre')
            ->paginate(15);

        return view('admin.personal.index', [
            'usuarios' => $usuarios,
            'rolesGestionables' => $rolesGestionables,
        ]);
    }

    /**
     * Muestra el formulario de alta de usuario operativo.
     */
    public function create(Request $request): View
    {
        return view('admin.personal.create', [
            'rolesGestionables' => $request->user()?->rolesGestionables() ?? collect(),
        ]);
    }

    /**
     * Crea un usuario operativo desde el panel.
     */
    public function store(GuardarUsuarioPersonalRequest $request): RedirectResponse
    {
        $datos = $request->datosUsuario();

        Usuario::query()->create([
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'rol' => $datos['rol'],
            'es_protegido' => false,
            'password' => Hash::make((string) $datos['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.personal.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    /**
     * Muestra la ficha del usuario operativo.
     */
    public function show(Request $request, Usuario $usuario): View
    {
        abort_unless($request->user()?->puedeGestionarUsuario($usuario), 403);

        return view('admin.personal.show', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * Muestra el formulario de edicion del usuario operativo.
     */
    public function edit(Request $request, Usuario $usuario): View
    {
        abort_unless($request->user()?->puedeGestionarUsuario($usuario), 403);

        return view('admin.personal.edit', [
            'usuario' => $usuario,
            'rolesGestionables' => $request->user()?->rolesGestionables() ?? collect(),
        ]);
    }

    /**
     * Actualiza un usuario operativo gestionable.
     */
    public function update(ActualizarUsuarioPersonalRequest $request, Usuario $usuario): RedirectResponse
    {
        $datos = $request->datosUsuario();

        if (isset($datos['password'])) {
            $datos['password'] = Hash::make((string) $datos['password']);
        }

        $usuario->update($datos);

        return redirect()->route('admin.personal.usuarios.show', $usuario)
            ->with('status', 'Usuario actualizado correctamente.');
    }

    /**
     * Elimina un usuario operativo mediante borrado logico.
     */
    public function destroy(Request $request, Usuario $usuario): RedirectResponse
    {
        abort_unless($request->user()?->puedeGestionarUsuario($usuario), 403);

        $usuario->delete();

        return redirect()->route('admin.personal.index')
            ->with('status', 'Usuario eliminado correctamente.');
    }
}
