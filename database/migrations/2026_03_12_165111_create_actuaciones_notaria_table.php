<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actuaciones_notaria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seguimiento_notaria_id')
                ->constrained('seguimientos_notaria')
                ->cascadeOnDelete();

            $table->date('fecha_actuacion');
            $table->text('descripcion_actuacion');

            // Estructura: juridico/notarias/{id_garantia}/actuaciones/{año}-{mes}/{archivo}
            $table->string('archivo_evidencia', 500)->nullable()
                ->comment('Ruta relativa en disco private. Ej: juridico/notarias/GAR-xxx/actuaciones/2026-02/archivo.pdf');

            $table->string('hubo_avance', 30)
                ->comment('EstatusAvanceEnum: SI|NO|EN_ESPERA_ACUERDO');

            $table->string('semana_label', 50)->nullable();

            $table->timestamps();

            $table->index('seguimiento_notaria_id');
            $table->index('fecha_actuacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actuaciones_notaria');
    }
};
