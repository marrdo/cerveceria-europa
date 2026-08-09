<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade identidad visual, SEO y recursos editables de la web pública.
     *
     * Los paths se guardan como TEXT para no agotar el límite de fila de
     * InnoDB con utf8mb4. Las comprobaciones hacen recuperable una ejecución
     * interrumpida por un DDL parcial de MySQL.
     */
    public function up(): void
    {
        $this->asegurarTexto('configuraciones_negocio', 'logo_path', 'descripcion_corta');
        $this->asegurarTexto('configuraciones_negocio', 'favicon_path', 'logo_path');
        $this->asegurarTexto('configuraciones_negocio', 'imagen_social_path', 'favicon_path');

        $this->agregarSiFalta('configuraciones_negocio', 'color_primario', fn (Blueprint $table) => $table->char('color_primario', 7)->default('#E3A13A')->after('imagen_social_path'));
        $this->agregarSiFalta('configuraciones_negocio', 'color_secundario', fn (Blueprint $table) => $table->char('color_secundario', 7)->default('#5D9B6E')->after('color_primario'));
        $this->agregarSiFalta('configuraciones_negocio', 'color_fondo', fn (Blueprint $table) => $table->char('color_fondo', 7)->default('#0F0A06')->after('color_secundario'));
        $this->agregarSiFalta('configuraciones_negocio', 'color_superficie', fn (Blueprint $table) => $table->char('color_superficie', 7)->default('#1F1812')->after('color_fondo'));
        $this->agregarSiFalta('configuraciones_negocio', 'color_texto', fn (Blueprint $table) => $table->char('color_texto', 7)->default('#F6ECD6')->after('color_superficie'));
        $this->agregarSiFalta('configuraciones_negocio', 'seo_titulo', fn (Blueprint $table) => $table->string('seo_titulo', 60)->nullable()->after('reservas_url'));
        $this->agregarSiFalta('configuraciones_negocio', 'seo_descripcion', fn (Blueprint $table) => $table->string('seo_descripcion', 160)->nullable()->after('seo_titulo'));
        $this->agregarSiFalta('configuraciones_negocio', 'seo_indexar', fn (Blueprint $table) => $table->boolean('seo_indexar')->default(false)->after('seo_descripcion'));
        $this->asegurarTexto('secciones_web', 'imagen_path', 'contenido');
    }

    public function down(): void
    {
        $this->eliminarSiExisten('secciones_web', ['imagen_path']);
        $this->eliminarSiExisten('configuraciones_negocio', [
            'logo_path', 'favicon_path', 'imagen_social_path', 'color_primario',
            'color_secundario', 'color_fondo', 'color_superficie', 'color_texto',
            'seo_titulo', 'seo_descripcion', 'seo_indexar',
        ]);
    }

    private function asegurarTexto(string $tabla, string $columna, string $despuesDe): void
    {
        Schema::table($tabla, function (Blueprint $table) use ($tabla, $columna, $despuesDe): void {
            $definicion = $table->text($columna)->nullable();

            Schema::hasColumn($tabla, $columna)
                ? $definicion->change()
                : $definicion->after($despuesDe);
        });
    }

    private function agregarSiFalta(string $tabla, string $columna, callable $definir): void
    {
        if (Schema::hasColumn($tabla, $columna)) {
            return;
        }

        Schema::table($tabla, $definir);
    }

    /** @param list<string> $columnas */
    private function eliminarSiExisten(string $tabla, array $columnas): void
    {
        $existentes = array_values(array_filter(
            $columnas,
            fn (string $columna): bool => Schema::hasColumn($tabla, $columna),
        ));

        if ($existentes !== []) {
            Schema::table($tabla, fn (Blueprint $table) => $table->dropColumn($existentes));
        }
    }
};
