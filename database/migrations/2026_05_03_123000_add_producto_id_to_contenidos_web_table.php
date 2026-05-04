<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula contenido publico con productos de inventario cuando aplique.
     */
    public function up(): void
    {
        Schema::table('contenidos_web', function (Blueprint $table): void {
            $table->foreignUuid('producto_id')
                ->nullable()
                ->after('tipo')
                ->constrained('productos')
                ->nullOnDelete();
        });
    }

    /**
     * Elimina la vinculacion con inventario.
     */
    public function down(): void
    {
        Schema::table('contenidos_web', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('producto_id');
        });
    }
};
