<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula pagos de comanda con el turno de caja activo.
     */
    public function up(): void
    {
        Schema::table('pagos_comanda', function (Blueprint $table): void {
            $table->foreignUuid('caja_turno_id')->nullable()->after('comanda_id')->constrained('turnos_caja')->nullOnDelete();
        });
    }

    /**
     * Elimina la vinculacion de pagos con caja.
     */
    public function down(): void
    {
        Schema::table('pagos_comanda', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('caja_turno_id');
        });
    }
};
