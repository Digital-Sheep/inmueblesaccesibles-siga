<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actuaciones_juicio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seguimiento_juicio_id')
                ->constrained('seguimientos_juicio')
                ->cascadeOnDelete();

            $table->date('fecha_actuacion');
            $table->text('descripcion_actuacion');

            // Almacena ruta relativa dentro del disco 'private'
            // Estructura: juridico/juicios/{id_garantia}/actuaciones/{año}-{mes}/{archivo}
            $table->string('archivo_evidencia', 500)->nullable()
                ->comment('Ruta relativa en disco private. Ej: juridico/juicios/GAR-xxx/actuaciones/2026-02/archivo.pdf');

            $table->string('hubo_avance', 30)
                ->comment('EstatusAvanceEnum: SI|NO|EN_ESPERA_ACUERDO');

            // Etiqueta visual de semana, solo referencia — no tiene lógica de negocio
            $table->string('semana_label', 50)->nullable()
                ->comment('Ej: SEMANA 16/02/2026 — solo referencia visual');

            $table->timestamps();

            // ── Índices ────────────────────────────────────────────────────────
            $table->index('seguimiento_juicio_id');
            $table->index('fecha_actuacion');
            $table->index('hubo_avance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actuaciones_juicio');
    }
};
