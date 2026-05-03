<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea devoluciones a proveedor y sus lineas.
     */
    public function up(): void
    {
        Schema::create('devoluciones_proveedor', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pedido_compra_id')->constrained('pedidos_compra')->cascadeOnDelete();
            $table->foreignUuid('proveedor_id')->constrained('proveedores');
            $table->string('numero', 50)->unique();
            $table->date('fecha_devolucion');
            $table->string('motivo', 191);
            $table->text('notas')->nullable();
            $table->foreignUuid('registrada_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lineas_devolucion_proveedor', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('devolucion_proveedor_id')->constrained('devoluciones_proveedor')->cascadeOnDelete();
            $table->foreignUuid('linea_pedido_compra_id')->constrained('lineas_pedido_compra')->cascadeOnDelete();
            $table->foreignUuid('producto_id')->constrained('productos');
            $table->foreignUuid('ubicacion_inventario_id')->constrained('ubicaciones_inventario');
            $table->foreignUuid('movimiento_inventario_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();
            $table->decimal('cantidad', 12, 3);
            $table->decimal('coste_unitario', 12, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Elimina devoluciones a proveedor.
     */
    public function down(): void
    {
        Schema::dropIfExists('lineas_devolucion_proveedor');
        Schema::dropIfExists('devoluciones_proveedor');
    }
};
