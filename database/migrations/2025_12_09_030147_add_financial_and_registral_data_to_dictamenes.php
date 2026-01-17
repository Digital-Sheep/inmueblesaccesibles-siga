<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dictamenes', function (Blueprint $table) {
            // 1. ANÁLISIS FINANCIERO DE LA DEUDA (Hoja Análisis Crédito)
            $table->decimal('monto_demandado_original', 15, 2)->nullable();
            $table->integer('mensualidades_vencidas')->nullable();
            $table->decimal('valor_cuota_mensual', 10, 2)->nullable();
            $table->decimal('intereses_anuales_estimados', 10, 2)->nullable();

            // 2. DATOS REGISTRALES (Hoja Dictaminación)
            $table->string('folio_real_rppc')->nullable(); // Folio en registro público
            $table->json('gravamenes_detectados')->nullable(); // Para guardar lista de hipotecas/embargos extra
            // Ej: [{"tipo": "Embargo", "acreedor": "Sindicato", "monto": 50000}]

            // 3. CONTROL
            $table->boolean('dictamen_registral_concluido')->default(false);
        });
    }
};
