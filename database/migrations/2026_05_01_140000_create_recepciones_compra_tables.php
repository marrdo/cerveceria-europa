<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas para recibir mercancia de pedidos de compra.
     */
    public function up(): void
    {
        Schema::create('recepciones_compra', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pedido_compra_id')->constrained('pedidos_compra')->cascadeOnDelete();
            $table->string('numero', 50)->unique();
            $table->date('fecha_recepcion');
            $table->text('notas')->nullable();
            $table->foreignUuid('recibido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lineas_recepcion_compra', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('recepcion_compra_id')->constrained('recepciones_compra')->cascadeOnDelete();
            $table->foreignUuid('linea_pedido_compra_id')->constrained('lineas_pedido_compra')->cascadeOnDelete();
            $table->foreignUuid('producto_id')->constrained('productos');
            $table->foreignUuid('ubicacion_inventario_id')->constrained('ubicaciones_inventario');
            $table->foreignUuid('movimiento_inventario_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();
            $table->decimal('cantidad', 12, 3);
            $table->decimal('coste_unitario', 12, 2)->nullable();
            $table->string('codigo_lote', 100)->nullable();
            $table->date('caduca_el')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Elimina las tablas de recepciones de compra.
     */
    public function down(): void
    {
        Schema::dropIfExists('lineas_recepcion_compra');
        Schema::dropIfExists('recepciones_compra');
    }
};
