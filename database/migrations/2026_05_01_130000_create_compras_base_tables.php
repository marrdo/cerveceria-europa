<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas base del modulo de compras.
     */
    public function up(): void
    {
        Schema::create('pedidos_compra', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('proveedor_id')->constrained('proveedores');
            $table->string('numero', 50)->unique();
            $table->string('estado', 30)->default('borrador')->index();
            $table->date('fecha_pedido')->nullable();
            $table->date('fecha_prevista')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->foreignUuid('creado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignUuid('actualizado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lineas_pedido_compra', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pedido_compra_id')->constrained('pedidos_compra')->cascadeOnDelete();
            $table->foreignUuid('producto_id')->constrained('productos');
            $table->string('descripcion', 191);
            $table->decimal('cantidad', 12, 3);
            $table->decimal('coste_unitario', 12, 2);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuestos', 12, 2);
            $table->decimal('total', 12, 2);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('eventos_pedido_compra', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pedido_compra_id')->constrained('pedidos_compra')->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->text('descripcion');
            $table->foreignUuid('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Elimina las tablas base del modulo de compras.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_pedido_compra');
        Schema::dropIfExists('lineas_pedido_compra');
        Schema::dropIfExists('pedidos_compra');
    }
};
