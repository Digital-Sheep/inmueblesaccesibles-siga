<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos_notaria', function (Blueprint $table) {
            $table->id();

            // ── Vinculación con SIGA (opcional en v1) ──────────────────────────
            $table->foreignId('propiedad_id')
                ->nullable()
                ->constrained('propiedades')
                ->nullOnDelete();

            $table->string('numero_credito', 100)->nullable()
                ->comment('Puente para vinculación futura con propiedades.numero_credito');

            $table->string('id_garantia', 100)->nullable();
            $table->string('nombre_cliente', 200)->nullable();

            // ── Datos específicos de notaría ───────────────────────────────────
            $table->string('notario', 200)->nullable();
            $table->string('numero_escritura', 100)->nullable();
            $table->date('fecha_escritura')->nullable();

            // ── Clasificación ──────────────────────────────────────────────────
            $table->string('sede', 30)
                ->comment('SedeJuicioEnum: MAZATLAN|GUADALAJARA|LA_PAZ|CDMX|CULIACAN');
            $table->string('administradora', 200)->nullable();

            // ── Cesión de derechos ─────────────────────────────────────────────
            $table->boolean('hay_cesion_derechos')->default(false);
            $table->string('cedente', 500)->nullable();
            $table->string('cesionario', 500)->nullable();

            // ── Seguimiento narrativo ──────────────────────────────────────────
            $table->text('etapa_actual')->nullable();
            $table->text('notas_director')->nullable();

            // ── Flags ──────────────────────────────────────────────────────────
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ────────────────────────────────────────────────────────
            $table->index('sede');
            $table->index('activo');
            $table->index('numero_credito');
            $table->index('id_garantia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos_notaria');
    }
};
