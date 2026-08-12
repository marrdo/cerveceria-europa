<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la configuracion transversal de la instalacion.
     */
    public function up(): void
    {
        Schema::create('configuraciones_negocio', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('clave', 50)->unique();
            $table->string('nombre_comercial', 191);
            $table->string('razon_social', 191)->nullable();
            $table->string('nif', 50)->nullable();
            $table->string('eslogan', 255)->nullable();
            $table->string('descripcion_corta', 500)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('pais', 100)->default('Espana');
            $table->text('horario')->nullable();
            $table->string('web_url', 2048)->nullable();
            $table->string('instagram_url', 2048)->nullable();
            $table->string('google_maps_url', 2048)->nullable();
            $table->string('reservas_url', 2048)->nullable();
            $table->string('zona_horaria', 100)->default('Europe/Madrid');
            $table->char('moneda', 3)->default('EUR');
            $table->timestamps();
        });
    }

    /**
     * Elimina la configuracion del negocio.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones_negocio');
    }
};
