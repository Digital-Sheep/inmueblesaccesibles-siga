<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            // Banderas de estado del precio
            $table->boolean('precio_calculado')
                ->default(false)
                ->after('cotizacion_activa_id')
                ->comment('Si ya se ejecutó el cotizador');

            $table->boolean('precio_aprobado')
                ->default(false)
                ->after('precio_calculado')
                ->comment('Si comercial y contabilidad ya aprobaron');

            $table->timestamp('precio_fecha_aprobacion')
                ->nullable()
                ->after('precio_aprobado')
                ->comment('Fecha en que se completaron las aprobaciones');

            $table->boolean('precio_requiere_decision_dge')
                ->default(false)
                ->after('precio_fecha_aprobacion')
                ->comment('Si hubo rechazos y DGE debe decidir');
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn([
                'precio_calculado',
                'precio_aprobado',
                'precio_fecha_aprobacion',
                'precio_requiere_decision_dge',
            ]);
        });
    }
};
