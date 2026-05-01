<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea trazabilidad de lotes para caducidad y rotacion FIFO/FEFO.
     */
    public function up(): void
    {
        Schema::create('lotes_inventario', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignUuid('ubicacion_inventario_id')->constrained('ubicaciones_inventario');
            $table->foreignUuid('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignUuid('movimiento_entrada_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();
            $table->string('codigo_lote', 100)->nullable();
            $table->decimal('cantidad_inicial', 12, 3);
            $table->decimal('cantidad_disponible', 12, 3);
            $table->date('recibido_el');
            $table->date('caduca_el')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['producto_id', 'ubicacion_inventario_id'], 'lotes_producto_ubicacion_index');
            $table->index(['caduca_el', 'cantidad_disponible'], 'lotes_caducidad_disponible_index');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table): void {
            $table->foreignUuid('lote_inventario_id')
                ->nullable()
                ->after('ubicacion_destino_id')
                ->constrained('lotes_inventario')
                ->nullOnDelete();
        });
    }

    /**
     * Elimina trazabilidad de lotes.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('lote_inventario_id');
        });

        Schema::dropIfExists('lotes_inventario');
    }
};
