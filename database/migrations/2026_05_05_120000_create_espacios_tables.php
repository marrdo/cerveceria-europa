<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la estructura fisica del negocio para separar sala de inventario.
     */
    public function up(): void
    {
        Schema::create('recintos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre_comercial', 191);
            $table->string('nombre_fiscal', 191)->nullable();
            $table->string('direccion', 191)->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->string('pais', 100)->default('Espana');
            $table->string('telefono', 30)->nullable();
            $table->string('email', 191)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('zonas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('recinto_id')->constrained('recintos')->cascadeOnDelete();
            $table->string('nombre', 191);
            $table->string('codigo', 50)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->text('notas')->nullable();
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['recinto_id', 'nombre']);
        });

        Schema::create('mesas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->string('nombre', 191);
            $table->unsignedSmallInteger('capacidad')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->text('notas')->nullable();
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['zona_id', 'nombre']);
        });
    }

    /**
     * Elimina la estructura fisica del negocio.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas');
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('recintos');
    }
};
