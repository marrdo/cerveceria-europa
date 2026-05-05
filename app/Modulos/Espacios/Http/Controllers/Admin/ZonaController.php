<?php

namespace App\Modulos\Espacios\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Espacios\Http\Requests\GuardarZonaRequest;
use App\Modulos\Espacios\Models\Recinto;
use App\Modulos\Espacios\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZonaController extends Controller
{
    /**
     * Lista zonas operativas.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'busqueda' => trim((string) $request->query('busqueda', '')),
            'activa' => (string) $request->query('activa', ''),
        ];

        $zonas = Zona::query()
            ->with(['recinto'])
            ->withCount('mesas')
            ->when($filtros['busqueda'] !== '', fn ($query) => $query->where('nombre', 'like', '%'.$filtros['busqueda'].'%'))
            ->when($filtros['activa'] !== '', fn ($query) => $query->where('activa', $filtros['activa'] === '1'))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('modulos.espacios.zonas.index', compact('zonas', 'filtros'));
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.espacios.zonas.form', [
            'zona' => new Zona(),
            'recintos' => Recinto::query()->where('activo', true)->orderBy('nombre_comercial')->get(),
        ]);
    }

    /**
     * Guarda una zona.
     */
    public function store(GuardarZonaRequest $request): RedirectResponse
    {
        Zona::query()->create($request->datos());

        return redirect()->route('admin.espacios.zonas.index')->with('status', 'Zona creada correctamente.');
    }

    /**
     * Formulario de edicion.
     */
    public function edit(Zona $zona): View
    {
        return view('modulos.espacios.zonas.form', [
            'zona' => $zona,
            'recintos' => Recinto::query()->where('activo', true)->orWhere('id', $zona->recinto_id)->orderBy('nombre_comercial')->get(),
        ]);
    }

    /**
     * Actualiza una zona.
     */
    public function update(GuardarZonaRequest $request, Zona $zona): RedirectResponse
    {
        $zona->update($request->datos());

        return redirect()->route('admin.espacios.zonas.index')->with('status', 'Zona actualizada correctamente.');
    }

    /**
     * Elimina logicamente una zona.
     */
    public function destroy(Zona $zona): RedirectResponse
    {
        $zona->delete();

        return redirect()->route('admin.espacios.zonas.index')->with('status', 'Zona eliminada correctamente.');
    }
}
