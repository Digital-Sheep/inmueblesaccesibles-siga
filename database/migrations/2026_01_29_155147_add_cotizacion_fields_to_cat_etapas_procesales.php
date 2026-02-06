<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cat_etapas_procesales', function (Blueprint $table) {
            // Campos para cotización de precios
            $table->enum('fase_cotizacion', ['FASE_1', 'FASE_2', 'FASE_3'])
                ->nullable()
                ->after('orden')
                ->comment('Para cotización: FASE_1=35%, FASE_2=20%, FASE_3=15%');

            $table->decimal('porcentaje_inversion', 5, 2)
                ->nullable()
                ->after('fase_cotizacion')
                ->comment('% de incremento por inversión para cotización');

            $table->boolean('aplica_para_cotizacion')
                ->default(false)
                ->after('porcentaje_inversion')
                ->comment('Si esta etapa se usa en el cotizador de precios');
        });
    }

    public function down(): void
    {
        Schema::table('cat_etapas_procesales', function (Blueprint $table) {
            $table->dropColumn([
                'fase_cotizacion',
                'porcentaje_inversion',
                'aplica_para_cotizacion',
            ]);
        });
    }
};
