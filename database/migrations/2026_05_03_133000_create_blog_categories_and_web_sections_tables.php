<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea categorias de blog y secciones editables de la web publica.
     */
    public function up(): void
    {
        Schema::create('categorias_blog', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nombre', 191);
            $table->string('slug', 191)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->unsignedInteger('orden')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categoria_blog_post', function (Blueprint $table): void {
            $table->foreignUuid('post_blog_id')->constrained('posts_blog')->cascadeOnDelete();
            $table->foreignUuid('categoria_blog_id')->constrained('categorias_blog')->cascadeOnDelete();
            $table->primary(['post_blog_id', 'categoria_blog_id']);
        });

        Schema::create('secciones_web', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('clave', 100)->unique();
            $table->string('nombre', 191);
            $table->string('titulo', 191)->nullable();
            $table->string('subtitulo', 500)->nullable();
            $table->longText('contenido')->nullable();
            $table->json('datos')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Elimina categorias y secciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('secciones_web');
        Schema::dropIfExists('categoria_blog_post');
        Schema::dropIfExists('categorias_blog');
    }
};
