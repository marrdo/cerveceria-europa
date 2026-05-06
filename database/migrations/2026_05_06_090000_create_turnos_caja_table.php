<?php

use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea los turnos de caja para control diario de cobros.
     */
    public function up(): void
    {
        Schema::create('turnos_caja', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('numero', 40)->unique();
            $table->foreignUuid('recinto_id')->nullable()->constrained('recintos')->nullOnDelete();
            $table->string('estado', 30)->default(EstadoTurnoCaja::Abierta->value)->index();
            $table->decimal('saldo_inicial', 12, 2)->default(0);
            $table->decimal('efectivo_esperado', 12, 2)->default(0);
            $table->decimal('efectivo_contado', 12, 2)->nullable();
            $table->decimal('descuadre', 12, 2)->default(0);
            $table->decimal('total_ventas', 12, 2)->default(0);
            $table->decimal('total_efectivo', 12, 2)->default(0);
            $table->decimal('total_tarjeta', 12, 2)->default(0);
            $table->decimal('total_bizum', 12, 2)->default(0);
            $table->decimal('total_invitacion', 12, 2)->default(0);
            $table->decimal('total_otro', 12, 2)->default(0);
            $table->decimal('total_cambio', 12, 2)->default(0);
            $table->unsignedInteger('pagos_count')->default(0);
            $table->foreignUuid('abierta_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignUuid('cerrada_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('abierta_at')->nullable()->index();
            $table->timestamp('cerrada_at')->nullable()->index();
            $table->text('notas_apertura')->nullable();
            $table->text('notas_cierre')->nullable();
            $table->timestamps();

            $table->index(['recinto_id', 'estado']);
        });
    }

    /**
     * Elimina los turnos de caja.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos_caja');
    }
};
