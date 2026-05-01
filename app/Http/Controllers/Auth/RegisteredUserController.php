<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\RolUsuario;
use App\Models\Usuario;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro.
     *
     * En este proyecto el registro publico queda deshabilitado desde rutas.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Gestiona una peticion de registro interna.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Usuario::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'rol' => RolUsuario::Camarero,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('admin.dashboard', absolute: false));
    }
}
