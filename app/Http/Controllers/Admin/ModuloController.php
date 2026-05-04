<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolUsuario;
use App\Http\Controllers\Controller;
use App\Models\Modulo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    /**
     * Activa o desactiva un modulo contratable del sistema.
     */
    public function toggle(Request $request, Modulo $modulo): RedirectResponse
    {
        abort_unless($request->user()?->rol === RolUsuario::Superadmin, 403);

        $modulo->update([
            'activo' => ! $modulo->activo,
        ]);

        return back()->with('status', 'Modulo actualizado correctamente.');
    }
}
