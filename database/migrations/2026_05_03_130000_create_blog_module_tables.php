<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea tablas base de modulos y del modulo opcional de blog.
     */
    public function up(): void
    {
        Schema::create('modulos_web_publica', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('clave', 100)->unique();
            $table->string('nombre', 191);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('posts_blog', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('titulo', 191);
            $table->string('slug', 191)->unique();
            $table->string('resumen', 500)->nullable();
            $table->longText('contenido');
            $table->string('imagen', 500)->nullable();
            $table->string('autor', 191)->nullable();
            $table->boolean('publicado')->default(true)->index();
            $table->boolean('destacado')->default(false)->index();
            $table->timestamp('publicado_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Elimina tablas del modulo de blog.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_blog');
        Schema::dropIfExists('modulos_web_publica');
    }
};
