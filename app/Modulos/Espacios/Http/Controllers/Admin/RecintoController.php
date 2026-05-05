<?php

namespace App\Modulos\Espacios\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Espacios\Http\Requests\GuardarRecintoRequest;
use App\Modulos\Espacios\Models\Recinto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecintoController extends Controller
{
    /**
     * Lista recintos configurados.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'busqueda' => trim((string) $request->query('busqueda', '')),
            'activo' => (string) $request->query('activo', ''),
        ];

        $recintos = Recinto::query()
            ->withCount('zonas')
            ->when($filtros['busqueda'] !== '', fn ($query) => $query->where('nombre_comercial', 'like', '%'.$filtros['busqueda'].'%'))
            ->when($filtros['activo'] !== '', fn ($query) => $query->where('activo', $filtros['activo'] === '1'))
            ->orderBy('nombre_comercial')
            ->paginate(15)
            ->withQueryString();

        return view('modulos.espacios.recintos.index', compact('recintos', 'filtros'));
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.espacios.recintos.form', ['recinto' => new Recinto()]);
    }

    /**
     * Guarda un recinto.
     */
    public function store(GuardarRecintoRequest $request): RedirectResponse
    {
        Recinto::query()->create($request->datos());

        return redirect()->route('admin.espacios.recintos.index')->with('status', 'Recinto creado correctamente.');
    }

    /**
     * Formulario de edicion.
     */
    public function edit(Recinto $recinto): View
    {
        return view('modulos.espacios.recintos.form', compact('recinto'));
    }

    /**
     * Actualiza un recinto.
     */
    public function update(GuardarRecintoRequest $request, Recinto $recinto): RedirectResponse
    {
        $recinto->update($request->datos());

        return redirect()->route('admin.espacios.recintos.index')->with('status', 'Recinto actualizado correctamente.');
    }

    /**
     * Elimina logicamente un recinto.
     */
    public function destroy(Recinto $recinto): RedirectResponse
    {
        $recinto->delete();

        return redirect()->route('admin.espacios.recintos.index')->with('status', 'Recinto eliminado correctamente.');
    }
}
