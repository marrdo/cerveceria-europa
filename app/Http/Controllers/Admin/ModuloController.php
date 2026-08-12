<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolUsuario;
use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Modulos\Sistema\Modulos\GestorModulos;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    /**
     * Activa o desactiva un modulo contratable del sistema.
     */
    public function toggle(Request $request, Modulo $modulo, GestorModulos $modulos): RedirectResponse
    {
        abort_unless($request->user()?->rol === RolUsuario::Superadmin, 403);

        try {
            $actualizado = $modulos->cambiarEstado($modulo, ! $modulo->activo);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with(
            'status',
            sprintf(
                '%s %s correctamente.',
                $actualizado->nombre,
                $actualizado->activo ? 'activado' : 'desactivado',
            ),
        );
    }
}
