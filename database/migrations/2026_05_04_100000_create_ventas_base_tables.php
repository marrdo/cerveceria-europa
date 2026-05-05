<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas base del modulo de ventas y comandas.
     */
    public function up(): void
    {
        Schema::create('comandas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('numero', 50)->unique();
            $table->string('mesa', 50)->nullable()->index();
            $table->string('cliente_nombre', 191)->nullable();
            $table->string('estado', 30)->default('abierta')->index();
            $table->foreignUuid('ubicacion_inventario_id')->nullable()->constrained('ubicaciones_inventario')->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->foreignUuid('creado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignUuid('actualizado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('servida_at')->nullable();
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lineas_comanda', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('comanda_id')->constrained('comandas')->cascadeOnDelete();
            $table->foreignUuid('contenido_web_id')->nullable()->constrained('contenidos_web')->nullOnDelete();
            $table->foreignUuid('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignUuid('movimiento_inventario_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();
            $table->string('nombre', 191);
            $table->decimal('cantidad', 12, 3);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('estado', 30)->default('pendiente')->index();
            $table->text('notas')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamp('servida_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Elimina las tablas base del modulo de ventas.
     */
    public function down(): void
    {
        Schema::dropIfExists('lineas_comanda');
        Schema::dropIfExists('comandas');
    }
};
