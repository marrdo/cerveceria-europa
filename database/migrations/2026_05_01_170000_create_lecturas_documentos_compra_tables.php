<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea tablas para lectura asistida de albaranes y facturas.
     */
    public function up(): void
    {
        Schema::create('documentos_compra', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->string('tipo_documento', 30)->index();
            $table->string('estado', 30)->default('pendiente')->index();
            $table->string('nombre_original', 191);
            $table->string('disco', 50)->default('local');
            $table->string('ruta_archivo', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->default(0);
            $table->text('notas')->nullable();
            $table->foreignUuid('subido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lecturas_documentos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('documento_compra_id')->constrained('documentos_compra')->cascadeOnDelete();
            $table->string('motor', 50)->default('pendiente');
            $table->string('estado', 30)->default('pendiente')->index();
            $table->longText('texto_extraido')->nullable();
            $table->json('datos_extraidos')->nullable();
            $table->text('mensaje_error')->nullable();
            $table->foreignUuid('procesado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('borradores_compra_documento', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('documento_compra_id')->constrained('documentos_compra')->cascadeOnDelete();
            $table->foreignUuid('pedido_compra_id')->nullable()->constrained('pedidos_compra')->nullOnDelete();
            $table->string('estado', 30)->default('pendiente_revision')->index();
            $table->json('datos_borrador')->nullable();
            $table->text('notas_revision')->nullable();
            $table->foreignUuid('revisado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Elimina tablas de lectura asistida.
     */
    public function down(): void
    {
        Schema::dropIfExists('borradores_compra_documento');
        Schema::dropIfExists('lecturas_documentos');
        Schema::dropIfExists('documentos_compra');
    }
};
