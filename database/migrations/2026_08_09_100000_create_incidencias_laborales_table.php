<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea incidencias multi-semana independientes de un cuadrante concreto.
     */
    public function up(): void
    {
        Schema::create('incidencias_laborales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('usuario_id')->nullable()->constrained('usuarios')->restrictOnDelete();
            $table->string('tipo', 30)->index();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('notas', 500)->nullable();
            $table->foreignUuid('creado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->index(['usuario_id', 'fecha_inicio', 'fecha_fin'], 'incidencias_usuario_periodo_index');
            $table->index(['fecha_inicio', 'fecha_fin'], 'incidencias_periodo_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias_laborales');
    }
};
