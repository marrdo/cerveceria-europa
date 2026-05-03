<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea incidencias de recepcion para documentar diferencias con proveedores.
     */
    public function up(): void
    {
        Schema::create('incidencias_recepcion_compra', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pedido_compra_id')->constrained('pedidos_compra')->cascadeOnDelete();
            $table->foreignUuid('recepcion_compra_id')->nullable()->constrained('recepciones_compra')->nullOnDelete();
            $table->foreignUuid('linea_pedido_compra_id')->nullable()->constrained('lineas_pedido_compra')->nullOnDelete();
            $table->string('tipo', 50)->index();
            $table->decimal('cantidad_afectada', 12, 3)->nullable();
            $table->text('descripcion');
            $table->boolean('resuelta')->default(false)->index();
            $table->foreignUuid('registrada_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Elimina incidencias de recepcion.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias_recepcion_compra');
    }
};
