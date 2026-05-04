<?php

namespace App\Modulos\WebPublica\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Http\Requests\GuardarSeccionWebRequest;
use App\Modulos\WebPublica\Models\SeccionWeb;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeccionWebController extends Controller
{
    public function index(): View
    {
        return view('modulos.web-publica.secciones.index', [
            'secciones' => SeccionWeb::query()->orderBy('nombre')->get(),
        ]);
    }

    public function edit(SeccionWeb $seccion): View
    {
        return view('modulos.web-publica.secciones.form', [
            'seccion' => $seccion,
        ]);
    }

    public function update(GuardarSeccionWebRequest $request, SeccionWeb $seccion): RedirectResponse
    {
        $seccion->update($request->datosSeccion());

        return redirect()->route('admin.web-publica.secciones.index')
            ->with('status', 'Seccion actualizada correctamente.');
    }
}
