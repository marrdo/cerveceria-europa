<?php

namespace App\Modulos\Inventario\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Rules\DocumentoIdentidadEspanol;
use App\Rules\TelefonoEspanol;
use App\Support\Validacion\ReglasValidacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class CatalogoInventarioController extends Controller
{
    /** @var class-string<Model> */
    protected string $modelo;

    protected string $rutaBase;

    protected string $titulo;

    /**
     * Lista registros de catalogo.
     */
    public function index(): View
    {
        return view('modulos.inventario.catalogo.index', [
            'items' => $this->modelo::query()->orderBy('nombre')->paginate(15),
            'titulo' => $this->titulo,
            'rutaBase' => $this->rutaBase,
        ]);
    }

    /**
     * Muestra formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.inventario.catalogo.form', [
            'item' => new $this->modelo(),
            'titulo' => $this->titulo,
            'rutaBase' => $this->rutaBase,
        ]);
    }

    /**
     * Guarda un registro de catalogo.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->modelo::query()->create($this->datosValidados($request));

        return redirect()->route($this->rutaBase.'.index')->with('status', $this->titulo.' creado correctamente.');
    }

    /**
     * Muestra formulario de edicion.
     */
    public function edit(string $item): View
    {
        $registro = $this->buscarRegistro($item);

        return view('modulos.inventario.catalogo.form', [
            'item' => $registro,
            'titulo' => $this->titulo,
            'rutaBase' => $this->rutaBase,
        ]);
    }

    /**
     * Actualiza un registro de catalogo.
     */
    public function update(Request $request, string $item): RedirectResponse
    {
        $registro = $this->buscarRegistro($item);
        $registro->update($this->datosValidados($request, $registro));

        return redirect()->route($this->rutaBase.'.index')->with('status', $this->titulo.' actualizado correctamente.');
    }

    /**
     * Elimina logicamente un registro de catalogo.
     */
    public function destroy(string $item): RedirectResponse
    {
        $this->buscarRegistro($item)->delete();

        return redirect()->route($this->rutaBase.'.index')->with('status', $this->titulo.' eliminado correctamente.');
    }

    /**
     * Valida y normaliza campos comunes de catalogo.
     *
     * @return array<string, mixed>
     */
    protected function datosValidados(Request $request, ?Model $item = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:191'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'cif_nif' => ReglasValidacion::documentoIdentidadEspanol(),
            'email' => ReglasValidacion::email(),
            'telefono' => ReglasValidacion::telefonoEspanol(),
            'persona_contacto' => ['nullable', 'string', 'max:191'],
            'descripcion' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],
            'permite_decimal' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $datos['activo'] = $request->boolean('activo', true);
        $datos['permite_decimal'] = $request->boolean('permite_decimal');

        if (array_key_exists('cif_nif', $datos)) {
            $datos['cif_nif'] = DocumentoIdentidadEspanol::normalizar($datos['cif_nif']);
        }

        if (array_key_exists('email', $datos)) {
            $datos['email'] = Str::lower($datos['email']);
        }

        if (array_key_exists('telefono', $datos)) {
            $datos['telefono'] = TelefonoEspanol::normalizar($datos['telefono']);
        }

        if (array_key_exists('codigo', $datos) && blank($datos['codigo'])) {
            $datos['codigo'] = Str::upper(Str::slug($datos['nombre'], '_'));
        }

        if ($this->usaSlug()) {
            $datos['slug'] = Str::slug($datos['nombre']);
        }

        return array_filter($datos, fn (mixed $valor): bool => $valor !== null);
    }

    /**
     * Indica si el modelo usa columna slug.
     */
    protected function usaSlug(): bool
    {
        return true;
    }

    /**
     * Busca el registro del catalogo actual.
     */
    private function buscarRegistro(string $id): Model
    {
        return $this->modelo::query()->findOrFail($id);
    }
}
