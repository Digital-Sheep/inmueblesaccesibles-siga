<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar nuevos campos
        Schema::table('procesos_venta', function (Blueprint $table) {
            // VISITA
            $table->dateTime('fecha_visita')->nullable();
            $table->text('observaciones_visita')->nullable();
            $table->string('resultado_visita')->nullable();

            // ENGANCHE
            $table->decimal('monto_enganche_solicitado', 15, 2)->nullable();
            $table->date('fecha_limite_enganche')->nullable();
            $table->date('fecha_pago_enganche')->nullable();

            // LIQUIDACIÓN
            $table->decimal('monto_liquidacion_solicitado', 15, 2)->nullable();
            $table->date('fecha_limite_liquidacion')->nullable();
            $table->date('fecha_pago_liquidacion')->nullable();

            // ESCRITURACIÓN Y ENTREGA
            $table->date('fecha_escrituracion')->nullable();
            $table->date('fecha_entrega_programada')->nullable();
            $table->date('fecha_entrega')->nullable();

            // CANCELACIÓN
            $table->string('motivo_cancelacion')->nullable();
            $table->date('fecha_cancelacion')->nullable();
            $table->text('detalles_cancelacion')->nullable();
        });

        // 2. Modificar el enum de estatus
        DB::statement("ALTER TABLE procesos_venta MODIFY COLUMN estatus ENUM(
            'ACTIVO',
            'VISITA_PROGRAMADA',
            'VISITA_REALIZADA',
            'APARTADO_GENERADO',
            'APARTADO_POR_VALIDAR',
            'APARTADO_VALIDADO',
            'EN_DICTAMINACION',
            'DICTAMINADO_POSITIVO',
            'DICTAMINADO_NEGATIVO',
            'ENGANCHE_SOLICITADO',
            'ENGANCHE_POR_VALIDAR',
            'ENGANCHE_PAGADO',
            'EN_PROCESO_COMPRA',
            'COMPRA_FINALIZADA',
            'LIQUIDACION_SOLICITADA',
            'LIQUIDACION_POR_VALIDAR',
            'LIQUIDACION_PAGADA',
            'EN_ESCRITURACION',
            'ESCRITURADO',
            'ENTREGA_PROGRAMADA',
            'ENTREGADO',
            'CANCELADO'
        ) NOT NULL DEFAULT 'ACTIVO'");
    }

    public function down(): void
    {
        Schema::table('procesos_venta', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_visita',
                'observaciones_visita',
                'resultado_visita',
                'monto_enganche_solicitado',
                'fecha_limite_enganche',
                'fecha_pago_enganche',
                'monto_liquidacion_solicitado',
                'fecha_limite_liquidacion',
                'fecha_pago_liquidacion',
                'fecha_escrituracion',
                'fecha_entrega_programada',
                'fecha_entrega',
                'motivo_cancelacion',
                'fecha_cancelacion',
                'detalles_cancelacion',
            ]);
        });

        // Restaurar enum original
        DB::statement("ALTER TABLE procesos_venta MODIFY COLUMN estatus ENUM(
            'ACTIVO',
            'APARTADO_GENERADO',
            'EN_DICTAMINACION',
            'CANCELADO'
        ) NOT NULL DEFAULT 'ACTIVO'");
    }
};
