<?php

namespace App\Modulos\WebPublica\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Http\Requests\GuardarContenidoWebRequest;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Models\Modulo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContenidoWebController extends Controller
{
    /**
     * Listado de contenido gestionable de la web publica.
     */
    public function index(): View
    {
        $filtros = [
            'tipo' => request('tipo', ''),
            'categoria_carta_id' => request('categoria_carta_id', ''),
            'publicado' => request('publicado', ''),
            'busqueda' => request('busqueda', ''),
        ];

        $contenidos = ContenidoWeb::query()
            ->with(['producto.stock', 'categoriaCarta.padre', 'tarifas'])
            ->when($filtros['tipo'] !== '', fn ($query) => $query->where('tipo', $filtros['tipo']))
            ->when($filtros['categoria_carta_id'] !== '', fn ($query) => $query->where('categoria_carta_id', $filtros['categoria_carta_id']))
            ->when($filtros['publicado'] !== '', fn ($query) => $query->where('publicado', $filtros['publicado'] === '1'))
            ->when($filtros['busqueda'] !== '', fn ($query) => $query->where('titulo', 'like', '%'.$filtros['busqueda'].'%'))
            ->orderBy('orden')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('modulos.web-publica.contenidos.index', [
            'contenidos' => $contenidos,
            'tipos' => TipoContenidoWeb::cases(),
            'categoriasCarta' => $this->categoriasCarta(),
            'filtros' => $filtros,
            'moduloWebPublica' => Modulo::query()->where('clave', 'web_publica')->first(),
            'moduloBlog' => Modulo::query()->where('clave', 'blog')->first(),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.web-publica.contenidos.form', [
            'contenido' => new ContenidoWeb(['publicado' => true]),
            'tipos' => TipoContenidoWeb::cases(),
            'productos' => $this->productosDisponibles(),
            'categoriasCarta' => $this->categoriasCarta(),
        ]);
    }

    /**
     * Guarda contenido nuevo.
     */
    public function store(GuardarContenidoWebRequest $request): RedirectResponse
    {
        $datos = $request->datosContenido();
        $datos['slug'] = $this->slugUnico($datos['titulo']);
        $datos['imagen'] = $this->guardarImagen($request);

        $contenido = ContenidoWeb::query()->create($datos);
        $this->sincronizarTarifas($contenido, $request->datosTarifas());

        return redirect()->route('admin.web-publica.contenidos.index')
            ->with('status', 'Contenido publicado correctamente.');
    }

    /**
     * Formulario de edicion.
     */
    public function edit(ContenidoWeb $contenido): View
    {
        return view('modulos.web-publica.contenidos.form', [
            'contenido' => $contenido->load(['producto', 'categoriaCarta', 'tarifas']),
            'tipos' => TipoContenidoWeb::cases(),
            'productos' => $this->productosDisponibles(),
            'categoriasCarta' => $this->categoriasCarta(),
        ]);
    }

    /**
     * Actualiza contenido existente.
     */
    public function update(GuardarContenidoWebRequest $request, ContenidoWeb $contenido): RedirectResponse
    {
        $datos = $request->datosContenido();

        if ($request->hasFile('imagen')) {
            $this->eliminarImagen($contenido);
            $datos['imagen'] = $this->guardarImagen($request);
        }

        $contenido->update($datos);
        $this->sincronizarTarifas($contenido, $request->datosTarifas());

        return redirect()->route('admin.web-publica.contenidos.index')
            ->with('status', 'Contenido actualizado correctamente.');
    }

    /**
     * Elimina contenido de la web publica.
     */
    public function destroy(ContenidoWeb $contenido): RedirectResponse
    {
        $this->eliminarImagen($contenido);
        $contenido->delete();

        return redirect()->route('admin.web-publica.contenidos.index')
            ->with('status', 'Contenido eliminado correctamente.');
    }

    /**
     * Cambia rapidamente publicado, destacado o fuera de carta.
     */
    public function toggle(ContenidoWeb $contenido, string $campo): RedirectResponse
    {
        abort_unless(in_array($campo, ['publicado', 'destacado', 'fuera_carta'], true), 404);

        $contenido->update([
            $campo => ! $contenido->{$campo},
        ]);

        return back()->with('status', 'Estado actualizado correctamente.');
    }

    /**
     * Guarda imagen publica.
     */
    private function guardarImagen(GuardarContenidoWebRequest $request): ?string
    {
        if (! $request->hasFile('imagen')) {
            return null;
        }

        return $request->file('imagen')->store('web-publica', 'public');
    }

    /**
     * Elimina imagen propia guardada en disco publico.
     */
    private function eliminarImagen(ContenidoWeb $contenido): void
    {
        if ($contenido->imagen && ! str_starts_with($contenido->imagen, 'http')) {
            Storage::disk('public')->delete($contenido->imagen);
        }
    }

    /**
     * Genera slug unico.
     */
    private function slugUnico(string $titulo): string
    {
        $base = Str::slug($titulo) ?: Str::uuid()->toString();
        $slug = $base;
        $contador = 2;

        while (ContenidoWeb::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$contador;
            $contador++;
        }

        return $slug;
    }

    /**
     * Productos que pueden alimentar carta publica.
     */
    private function productosDisponibles()
    {
        return Producto::query()
            ->where('activo', true)
            ->with(['unidad', 'stock'])
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Reemplaza las tarifas editables de un contenido.
     *
     * @param array<int, array<string, mixed>> $tarifas
     */
    private function sincronizarTarifas(ContenidoWeb $contenido, array $tarifas): void
    {
        $contenido->tarifas()->delete();

        foreach ($tarifas as $tarifa) {
            $contenido->tarifas()->create($tarifa);
        }
    }

    /**
     * Categorias activas disponibles para ordenar la carta publica.
     */
    private function categoriasCarta()
    {
        return CategoriaCarta::query()
            ->with('padre')
            ->where('activo', true)
            ->orderByRaw('categoria_padre_id is not null')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }
}
