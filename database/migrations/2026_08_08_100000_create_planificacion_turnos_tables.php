<?php

use App\Modulos\PlanificacionTurnos\Enums\EstadoCuadranteLaboral;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la base del módulo de planificación laboral.
     */
    public function up(): void
    {
        Schema::create('areas_trabajo', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre', 100)->unique();
            $table->char('color', 7)->default('#2563EB');
            $table->boolean('activo')->default(true)->index();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('cuadrantes_laborales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->date('semana_inicio')->unique();
            $table->string('estado', 20)->default(EstadoCuadranteLaboral::Borrador->value)->index();
            $table->text('notas')->nullable();
            $table->timestamp('publicado_at')->nullable()->index();
            $table->foreignUuid('publicado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('jornadas_laborales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cuadrante_laboral_id')->constrained('cuadrantes_laborales')->cascadeOnDelete();
            $table->foreignUuid('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignUuid('area_trabajo_id')->nullable()->constrained('areas_trabajo')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('termina_dia_siguiente')->default(false);
            $table->unsignedSmallInteger('minutos_descanso')->default(0);
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'fecha']);
            $table->index(['cuadrante_laboral_id', 'fecha']);
            $table->index(['area_trabajo_id', 'fecha']);
        });
    }

    /**
     * Elimina las tablas en orden inverso a sus dependencias.
     */
    public function down(): void
    {
        Schema::dropIfExists('jornadas_laborales');
        Schema::dropIfExists('cuadrantes_laborales');
        Schema::dropIfExists('areas_trabajo');
    }
};
