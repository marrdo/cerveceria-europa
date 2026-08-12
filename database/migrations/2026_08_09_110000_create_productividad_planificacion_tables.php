<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->unsignedSmallInteger('minutos_contrato_semanales')->default(2400);
        });

        Schema::create('coberturas_minimas_laborales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('area_trabajo_id')->constrained('areas_trabajo')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedTinyInteger('minimo_personas')->default(1);
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();

            $table->index(['dia_semana', 'area_trabajo_id'], 'cobertura_dia_area_index');
        });

        Schema::create('plantillas_cuadrantes_laborales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre', 120)->unique();
            $table->string('descripcion', 500)->nullable();
            $table->foreignUuid('creado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('plantillas_jornadas_laborales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('plantilla_id')->constrained('plantillas_cuadrantes_laborales')->cascadeOnDelete();
            $table->foreignUuid('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignUuid('area_trabajo_id')->nullable()->constrained('areas_trabajo')->nullOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('termina_dia_siguiente')->default(false);
            $table->unsignedSmallInteger('minutos_descanso')->default(0);
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            $table->index(['plantilla_id', 'dia_semana'], 'plantilla_jornadas_dia_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_jornadas_laborales');
        Schema::dropIfExists('plantillas_cuadrantes_laborales');
        Schema::dropIfExists('coberturas_minimas_laborales');

        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dropColumn('minutos_contrato_semanales');
        });
    }
};
