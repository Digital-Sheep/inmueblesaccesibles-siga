<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            // Campos de entrada para el cálculo
            $table->enum('tamano_propiedad', ['CHICA', 'MEDIANA', 'GRANDE', 'MUY_GRANDE'])
                ->nullable()
                ->after('construccion_m2')
                ->comment('Auto-calculado o seleccionado manual');

            $table->foreignId('etapa_procesal_id')
                ->nullable()
                ->after('etapa_judicial_reportada')
                ->constrained('cat_etapas_procesales')
                ->nullOnDelete()
                ->comment('Etapa procesal para calcular % inversión');

            // PRECIOS ACTIVOS (copiados desde la cotización activa para performance)
            $table->decimal('precio_sin_remodelacion', 12, 2)->nullable()
                ->after('precio_venta_sugerido')
                ->comment('Base + costos sin remodelación + inversión');

            $table->decimal('precio_venta_con_descuento', 12, 2)->nullable()
                ->after('precio_sin_remodelacion')
                ->comment('Precio final con descuento');

            $table->decimal('porcentaje_descuento', 5, 2)->nullable()
                ->after('precio_venta_con_descuento')
                ->comment('% de descuento aplicado');

            $table->decimal('porcentaje_utilidad', 5, 2)->nullable()
                ->after('porcentaje_descuento')
                ->comment('% utilidad = (Precio con desc - Costo total) / Precio con desc * 100');

            // Referencia a la cotización activa (para ver desglose completo)
            $table->foreignId('cotizacion_activa_id')
                ->nullable()
                ->after('porcentaje_utilidad')
                ->constrained('cotizaciones')
                ->nullOnDelete()
                ->comment('FK a la cotización vigente con desglose completo');

            // Control de precio custom (solo DGE después de primera aprobación)
            $table->decimal('precio_custom_solicitado', 12, 2)->nullable()
                ->after('cotizacion_activa_id')
                ->comment('Si DGE modifica manualmente el precio');

            $table->text('precio_custom_justificacion')->nullable()
                ->after('precio_custom_solicitado')
                ->comment('Motivo del cambio de precio');

            $table->foreignId('precio_custom_solicitante_id')
                ->nullable()
                ->after('precio_custom_justificacion')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('precio_custom_fecha')->nullable()
                ->after('precio_custom_solicitante_id');
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropForeign(['etapa_procesal_id']);
            $table->dropForeign(['cotizacion_activa_id']);
            $table->dropForeign(['precio_custom_solicitante_id']);

            $table->dropColumn([
                'tamano_propiedad',
                'etapa_procesal_id',
                'precio_sin_remodelacion',
                'precio_venta_con_descuento',
                'porcentaje_descuento',
                'porcentaje_utilidad',
                'cotizacion_activa_id',
                'precio_custom_solicitado',
                'precio_custom_justificacion',
                'precio_custom_solicitante_id',
                'precio_custom_fecha',
            ]);
        });
    }
};
