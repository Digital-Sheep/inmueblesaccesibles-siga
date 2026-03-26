<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actuaciones_dictamen', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seguimiento_dictamen_id')
                  ->constrained('seguimientos_dictamen')
                  ->cascadeOnDelete();

            $table->date('fecha_actuacion');
            $table->date('fecha_proxima_actuacion')->nullable();
            $table->text('descripcion_actuacion');
            $table->text('etapa_actual')->nullable()
                  ->comment('Si se llena, propaga a seguimientos_dictamen.etapa_actual');

            $table->string('archivo_evidencia', 500)->nullable()
                  ->comment('Disco private: juridico/dictamenes/{id}/actuaciones/{año}-{mes}/');

            $table->string('hubo_avance', 30)
                  ->comment('EstatusAvanceEnum: SI|NO|EN_ESPERA_ACUERDO');

            $table->string('semana_label', 50)->nullable()
                  ->comment('Auto-generado por Observer. Ej: SEMANA 25/03/2026');

            $table->timestamps();

            $table->index('seguimiento_dictamen_id');
            $table->index('fecha_actuacion');
            $table->index('fecha_proxima_actuacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actuaciones_dictamen');
    }
};
