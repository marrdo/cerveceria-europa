<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea tarifas multiples para productos publicados en carta.
     */
    public function up(): void
    {
        Schema::create('tarifas_contenido_web', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('contenido_web_id')->constrained('contenidos_web')->cascadeOnDelete();
            $table->string('nombre', 80)->nullable();
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('orden')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Elimina tarifas multiples.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas_contenido_web');
    }
};
