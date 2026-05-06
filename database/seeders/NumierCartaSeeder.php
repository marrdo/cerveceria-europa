<?php

namespace Database\Seeders;

use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Modulos\WebPublica\Models\TarifaContenidoWeb;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class NumierCartaSeeder extends Seeder
{
    /**
     * Importa la carta real exportada desde los AJAX de Numier.
     */
    public function run(): void
    {
        $bloques = $this->leerBloquesNumier();

        DB::transaction(function () use ($bloques): void {
            $this->limpiarCartaActual();

            foreach ($bloques as $bloque) {
                $this->importarBloque($bloque);
            }
        });
    }

    /**
     * Limpia contenidos demo de carta y categorias para trabajar con carta real.
     */
    private function limpiarCartaActual(): void
    {
        TarifaContenidoWeb::query()->delete();
        ContenidoWeb::withTrashed()->forceDelete();
        CategoriaCarta::query()->update(['categoria_padre_id' => null]);
        CategoriaCarta::withTrashed()->forceDelete();
    }

    /**
     * Lee todos los bloques JSON incluidos en el fichero de Numier.
     *
     * @return array<int, array{etiqueta: string, payload: array<string, mixed>}>
     */
    private function leerBloquesNumier(): array
    {
        $ruta = database_path('../Cartasdesdenumier.txt');

        if (! File::exists($ruta)) {
            throw new RuntimeException('No se ha encontrado el fichero Cartasdesdenumier.txt en la raiz del proyecto.');
        }

        $contenido = File::get($ruta);
        preg_match_all('/^([^\r\n{}][^\r\n]*)\R(\{.*?)(?=^\S[^\r\n{}]*\R\{|\\z)/msu', $contenido, $coincidencias, PREG_SET_ORDER);

        return collect($coincidencias)
            ->map(function (array $coincidencia): array {
                $payload = json_decode($coincidencia[2], true, 512, JSON_THROW_ON_ERROR);

                return [
                    'etiqueta' => trim((string) $coincidencia[1]),
                    'payload' => $payload,
                ];
            })
            ->filter(fn (array $bloque): bool => ($bloque['payload']['statusCode'] ?? null) === 200)
            ->values()
            ->all();
    }

    /**
     * Importa una seccion padre de carta con sus categorias hijas y productos.
     *
     * @param array{etiqueta: string, payload: array<string, mixed>} $bloque
     */
    private function importarBloque(array $bloque): void
    {
        $categorias = $bloque['payload']['data']['content'] ?? [];

        if (! is_array($categorias) || $categorias === []) {
            return;
        }

        $primeraCategoria = $categorias[0];
        $nombrePadre = $primeraCategoria['nombrePadre'] ?? $bloque['etiqueta'];
        $idPadre = $primeraCategoria['idPadre'] ?? Str::slug((string) $nombrePadre);

        $categoriaPadre = CategoriaCarta::query()->create([
            'nombre' => Str::headline(Str::lower((string) $nombrePadre)),
            'slug' => $this->slugCategoria((string) $nombrePadre, (string) $idPadre),
            'descripcion' => $this->descripcionPadre((string) $nombrePadre),
            'activo' => true,
            'orden' => $this->ordenPadre((string) $nombrePadre),
        ]);

        foreach ($categorias as $categoria) {
            $categoriaHija = CategoriaCarta::query()->create([
                'categoria_padre_id' => $categoriaPadre->id,
                'nombre' => Str::headline(Str::lower((string) ($categoria['name'] ?? 'Sin categoria'))),
                'slug' => $this->slugCategoria((string) ($categoria['name'] ?? 'sin-categoria'), (string) ($categoria['idCategoria'] ?? Str::uuid())),
                'descripcion' => trim((string) ($categoria['descripcion'] ?? '')) ?: null,
                'activo' => true,
                'orden' => (int) ($categoria['orden'] ?? 0),
            ]);

            foreach (($categoria['products'] ?? []) as $indice => $producto) {
                $this->importarProducto($producto, $categoriaHija, $categoriaPadre, $indice);
            }
        }
    }

    /**
     * Importa un producto de Numier como contenido editable de carta.
     *
     * @param array<string, mixed> $producto
     */
    private function importarProducto(array $producto, CategoriaCarta $categoria, CategoriaCarta $categoriaPadre, int $indice): void
    {
        $rates = collect($producto['rates'] ?? [])
            ->filter(fn (array $rate): bool => isset($rate['price']) && is_numeric($rate['price']))
            ->values();

        $precioBase = $rates->isNotEmpty()
            ? (float) $rates->first()['price']
            : null;

        $contenido = ContenidoWeb::query()->create([
            'tipo' => $this->tipoDesdeCategoriaPadre($categoriaPadre->nombre),
            'categoria_carta_id' => $categoria->id,
            'titulo' => trim((string) ($producto['name'] ?? 'Producto sin nombre')),
            'slug' => $this->slugProducto((string) ($producto['name'] ?? 'producto'), (string) ($producto['id'] ?? Str::uuid())),
            'descripcion_corta' => trim((string) ($producto['description'] ?? '')) ?: null,
            'contenido' => null,
            'precio' => $precioBase,
            'alergenos' => $this->alergenos($producto['allergens'] ?? null),
            'imagen' => trim((string) ($producto['image'] ?? '')) ?: null,
            'destacado' => (bool) ($producto['isFeatured'] ?? false),
            'fuera_carta' => Str::contains(Str::lower($categoria->nombre), 'fuera de carta'),
            'publicado' => ! (bool) ($producto['noAvailable'] ?? false),
            'orden' => $indice,
        ]);

        foreach ($rates as $orden => $rate) {
            $contenido->tarifas()->create([
                'nombre' => trim((string) ($rate['nameprice'] ?? '')) ?: null,
                'precio' => (float) $rate['price'],
                'orden' => $orden,
            ]);
        }
    }

    /**
     * Devuelve el tipo interno segun la seccion padre de Numier.
     */
    private function tipoDesdeCategoriaPadre(string $nombrePadre): TipoContenidoWeb
    {
        return match (Str::upper($nombrePadre)) {
            'CERVEZAS' => TipoContenidoWeb::Cerveza,
            'BEBIDAS' => TipoContenidoWeb::Bebida,
            default => TipoContenidoWeb::Plato,
        };
    }

    /**
     * Normaliza el slug de una categoria usando el identificador de Numier.
     */
    private function slugCategoria(string $nombre, string $idNumier): string
    {
        return Str::slug($nombre).'-numier-'.$idNumier;
    }

    /**
     * Normaliza el slug de un producto usando el identificador de Numier.
     */
    private function slugProducto(string $nombre, string $idNumier): string
    {
        return Str::limit(Str::slug($nombre), 150, '').'-numier-'.$idNumier;
    }

    /**
     * Describe brevemente la seccion padre.
     */
    private function descripcionPadre(string $nombrePadre): ?string
    {
        return match (Str::upper($nombrePadre)) {
            'COMIDAS' => 'Cocina de bar para compartir y maridar con cerveza.',
            'CERVEZAS' => 'Carta de cervezas organizada por estilos y formatos.',
            'BEBIDAS' => 'Bebidas, vinos, copas, cafes e infusiones.',
            default => null,
        };
    }

    /**
     * Orden estable de las secciones principales.
     */
    private function ordenPadre(string $nombrePadre): int
    {
        return match (Str::upper($nombrePadre)) {
            'COMIDAS' => 10,
            'CERVEZAS' => 20,
            'BEBIDAS' => 30,
            default => 99,
        };
    }

    /**
     * Conserva el codigo de alergenos de Numier sin inventar una traduccion incompleta.
     *
     * @return array<int, string>|null
     */
    private function alergenos(mixed $codigo): ?array
    {
        $codigo = trim((string) $codigo);

        if ($codigo === '' || $codigo === str_repeat('0', strlen($codigo))) {
            return null;
        }

        return ['numier:'.$codigo];
    }
}
