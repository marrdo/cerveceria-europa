<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea categorias jerarquicas para organizar la carta publica.
     */
    public function up(): void
    {
        Schema::create('categorias_carta', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('categoria_padre_id')->nullable()->constrained('categorias_carta')->nullOnDelete();
            $table->string('nombre', 191);
            $table->string('slug', 191)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->unsignedInteger('orden')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('contenidos_web', function (Blueprint $table): void {
            $table->foreignUuid('categoria_carta_id')
                ->nullable()
                ->after('producto_id')
                ->constrained('categorias_carta')
                ->nullOnDelete();
        });
    }

    /**
     * Elimina categorias de carta y su relacion con contenidos.
     */
    public function down(): void
    {
        Schema::table('contenidos_web', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('categoria_carta_id');
        });

        Schema::dropIfExists('categorias_carta');
    }
};
