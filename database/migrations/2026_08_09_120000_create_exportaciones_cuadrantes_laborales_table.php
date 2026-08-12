<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conserva cada Excel generado como una evidencia inmutable de publicación.
     */
    public function up(): void
    {
        Schema::create('exportaciones_cuadrantes_laborales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cuadrante_laboral_id')->constrained('cuadrantes_laborales')->cascadeOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('disk', 50);
            $table->string('ruta', 500);
            $table->string('nombre_archivo', 191);
            $table->string('mime_type', 100)->default('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $table->unsignedBigInteger('tamano_bytes');
            $table->char('hash_sha256', 64);
            $table->foreignUuid('generado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('generado_at')->index();
            $table->timestamps();

            $table->unique(['cuadrante_laboral_id', 'version'], 'exportacion_cuadrante_version_unique');
        });
    }

    /**
     * Elimina el historial de exportaciones de cuadrantes.
     */
    public function down(): void
    {
        Schema::dropIfExists('exportaciones_cuadrantes_laborales');
    }
};
