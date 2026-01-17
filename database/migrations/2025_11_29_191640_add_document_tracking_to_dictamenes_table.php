<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dictamenes', function (Blueprint $table) {
            // Para saber en qué paso va la obtención de papeles
            $table->enum('sub_etapa', ['POR_INICIAR', 'SOLICITUD_ENVIADA', 'ESPERANDO_RESPUESTA', 'DOCUMENTOS_RECIBIDOS'])
                ->default('POR_INICIAR')
                ->after('estatus');

            // Fechas de control (Semáforos del Excel)
            $table->date('fecha_solicitud_documentos')->nullable(); // Cuando se pidió al Banco/RPPC
            $table->date('fecha_recepcion_documentos')->nullable(); // Cuando llegaron

            // Para saber qué estamos esperando (CLG, Expediente, Ambos)
            $table->string('documento_esperado')->nullable();
        });
    }
};
