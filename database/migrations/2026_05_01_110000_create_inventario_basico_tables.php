<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas base del modulo de inventario.
     */
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre', 191);
            $table->string('slug', 191)->unique();
            $table->string('cif_nif', 50)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('persona_contacto', 191)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categorias_productos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre', 191);
            $table->string('slug', 191)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('unidades_inventario', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre', 191);
            $table->string('codigo', 30)->unique();
            $table->boolean('permite_decimal')->default(false);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ubicaciones_inventario', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre', 191);
            $table->string('codigo', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('productos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('categoria_producto_id')->constrained('categorias_productos');
            $table->foreignUuid('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignUuid('unidad_inventario_id')->constrained('unidades_inventario');
            $table->string('nombre', 191);
            $table->string('sku', 100)->nullable()->unique();
            $table->string('codigo_barras', 100)->nullable()->index();
            $table->string('referencia_proveedor', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->decimal('precio_coste', 12, 2)->nullable();
            $table->boolean('controla_stock')->default(true);
            $table->boolean('controla_caducidad')->default(false);
            $table->decimal('cantidad_alerta_stock', 12, 3)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_inventario', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignUuid('ubicacion_inventario_id')->constrained('ubicaciones_inventario');
            $table->decimal('cantidad', 12, 3)->default(0);
            $table->decimal('cantidad_minima', 12, 3)->default(0);
            $table->timestamps();

            $table->unique(['producto_id', 'ubicacion_inventario_id'], 'stock_producto_ubicacion_unique');
        });

        Schema::create('movimientos_inventario', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignUuid('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignUuid('ubicacion_inventario_id')->nullable()->constrained('ubicaciones_inventario');
            $table->foreignUuid('ubicacion_origen_id')->nullable()->constrained('ubicaciones_inventario');
            $table->foreignUuid('ubicacion_destino_id')->nullable()->constrained('ubicaciones_inventario');
            $table->string('tipo', 30)->index();
            $table->decimal('cantidad', 12, 3);
            $table->decimal('stock_antes', 12, 3);
            $table->decimal('stock_despues', 12, 3);
            $table->decimal('coste_unitario', 12, 2)->nullable();
            $table->string('motivo', 191);
            $table->string('referencia', 191)->nullable();
            $table->date('caduca_el')->nullable();
            $table->text('notas')->nullable();
            $table->foreignUuid('creado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Elimina las tablas base del modulo de inventario.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('stock_inventario');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('ubicaciones_inventario');
        Schema::dropIfExists('unidades_inventario');
        Schema::dropIfExists('categorias_productos');
        Schema::dropIfExists('proveedores');
    }
};
