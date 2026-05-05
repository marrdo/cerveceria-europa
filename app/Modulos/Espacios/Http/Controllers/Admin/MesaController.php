<?php

namespace App\Modulos\Espacios\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Espacios\Http\Requests\GuardarMesaRequest;
use App\Modulos\Espacios\Models\Mesa;
use App\Modulos\Espacios\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MesaController extends Controller
{
    /**
     * Lista mesas y puestos de servicio.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'busqueda' => trim((string) $request->query('busqueda', '')),
            'activa' => (string) $request->query('activa', ''),
        ];

        $mesas = Mesa::query()
            ->with(['zona.recinto'])
            ->when($filtros['busqueda'] !== '', fn ($query) => $query->where('nombre', 'like', '%'.$filtros['busqueda'].'%'))
            ->when($filtros['activa'] !== '', fn ($query) => $query->where('activa', $filtros['activa'] === '1'))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('modulos.espacios.mesas.index', compact('mesas', 'filtros'));
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.espacios.mesas.form', [
            'mesa' => new Mesa(),
            'zonas' => Zona::query()->with('recinto')->where('activa', true)->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Guarda una mesa.
     */
    public function store(GuardarMesaRequest $request): RedirectResponse
    {
        Mesa::query()->create($request->datos());

        return redirect()->route('admin.espacios.mesas.index')->with('status', 'Mesa creada correctamente.');
    }

    /**
     * Formulario de edicion.
     */
    public function edit(Mesa $mesa): View
    {
        return view('modulos.espacios.mesas.form', [
            'mesa' => $mesa,
            'zonas' => Zona::query()->with('recinto')->where('activa', true)->orWhere('id', $mesa->zona_id)->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Actualiza una mesa.
     */
    public function update(GuardarMesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $mesa->update($request->datos());

        return redirect()->route('admin.espacios.mesas.index')->with('status', 'Mesa actualizada correctamente.');
    }

    /**
     * Elimina logicamente una mesa.
     */
    public function destroy(Mesa $mesa): RedirectResponse
    {
        $mesa->delete();

        return redirect()->route('admin.espacios.mesas.index')->with('status', 'Mesa eliminada correctamente.');
    }
}
