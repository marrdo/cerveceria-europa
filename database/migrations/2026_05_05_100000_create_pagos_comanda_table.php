<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea los pagos asociados a comandas del modulo de ventas.
     */
    public function up(): void
    {
        Schema::create('pagos_comanda', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('comanda_id')->constrained('comandas')->cascadeOnDelete();
            $table->string('metodo', 30)->index();
            $table->decimal('importe', 12, 2);
            $table->decimal('recibido', 12, 2)->nullable();
            $table->decimal('cambio', 12, 2)->default(0);
            $table->string('referencia', 191)->nullable();
            $table->text('notas')->nullable();
            $table->foreignUuid('cobrado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('cobrado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Elimina los pagos de comandas.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_comanda');
    }
};
