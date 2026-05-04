<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generaliza la tabla de modulos para todo el panel.
     */
    public function up(): void
    {
        if (Schema::hasTable('modulos_web_publica') && ! Schema::hasTable('modulos')) {
            Schema::rename('modulos_web_publica', 'modulos');
        }

        Schema::table('modulos', function (Blueprint $table): void {
            if (! Schema::hasColumn('modulos', 'grupo')) {
                $table->string('grupo', 80)->default('panel')->after('descripcion')->index();
            }

            if (! Schema::hasColumn('modulos', 'orden')) {
                $table->unsignedInteger('orden')->default(0)->after('activo')->index();
            }
        });
    }

    /**
     * Recupera el nombre anterior si se revierte la migracion.
     */
    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table): void {
            if (Schema::hasColumn('modulos', 'orden')) {
                $table->dropColumn('orden');
            }

            if (Schema::hasColumn('modulos', 'grupo')) {
                $table->dropColumn('grupo');
            }
        });

        if (Schema::hasTable('modulos') && ! Schema::hasTable('modulos_web_publica')) {
            Schema::rename('modulos', 'modulos_web_publica');
        }
    }
};
