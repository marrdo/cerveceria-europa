<?php

namespace App\Modulos\WebPublica\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Http\Requests\GuardarSeccionWebRequest;
use App\Modulos\WebPublica\Models\SeccionWeb;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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
        $datos = $request->datosSeccion();

        if ($request->boolean('eliminar_imagen')) {
            $this->eliminarImagen($seccion->imagen_path);
            $datos['imagen_path'] = null;
        } elseif ($request->hasFile('imagen')) {
            $this->eliminarImagen($seccion->imagen_path);
            $datos['imagen_path'] = $request->file('imagen')->store('web-publica/secciones', 'public');
        }

        $seccion->update($datos);

        return redirect()->route('admin.web-publica.secciones.index')
            ->with('status', 'Seccion actualizada correctamente.');
    }

    private function eliminarImagen(?string $ruta): void
    {
        if (filled($ruta) && ! str_starts_with($ruta, 'http://') && ! str_starts_with($ruta, 'https://')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
