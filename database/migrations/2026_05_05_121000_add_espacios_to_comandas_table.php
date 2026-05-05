<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula comandas con recinto, zona y mesa sin mezclarlo con inventario.
     */
    public function up(): void
    {
        Schema::table('comandas', function (Blueprint $table): void {
            $table->foreignUuid('recinto_id')->nullable()->after('cliente_nombre')->constrained('recintos')->nullOnDelete();
            $table->foreignUuid('zona_id')->nullable()->after('recinto_id')->constrained('zonas')->nullOnDelete();
            $table->foreignUuid('mesa_id')->nullable()->after('zona_id')->constrained('mesas')->nullOnDelete();
        });
    }

    /**
     * Elimina la vinculacion con espacios.
     */
    public function down(): void
    {
        Schema::table('comandas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('mesa_id');
            $table->dropConstrainedForeignId('zona_id');
            $table->dropConstrainedForeignId('recinto_id');
        });
    }
};
