<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea contenidos editables para la web publica.
     */
    public function up(): void
    {
        Schema::create('contenidos_web', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tipo', 50)->index();
            $table->string('titulo', 191);
            $table->string('slug', 191)->unique();
            $table->string('descripcion_corta', 500)->nullable();
            $table->longText('contenido')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->json('alergenos')->nullable();
            $table->string('imagen', 500)->nullable();
            $table->boolean('destacado')->default(false)->index();
            $table->boolean('fuera_carta')->default(false)->index();
            $table->boolean('publicado')->default(true)->index();
            $table->unsignedInteger('orden')->default(0)->index();
            $table->date('publicado_desde')->nullable();
            $table->date('publicado_hasta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Elimina contenidos editables de la web publica.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenidos_web');
    }
};
