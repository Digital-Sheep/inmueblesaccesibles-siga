<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actuaciones_juicio', function (Blueprint $table) {
            $table->date('fecha_proxima_actuacion')
                  ->nullable()
                  ->after('fecha_actuacion')
                  ->comment('Fecha estimada de la próxima actuación. Dispara notificación adicional.');
        });

        Schema::table('actuaciones_notaria', function (Blueprint $table) {
            $table->date('fecha_proxima_actuacion')
                  ->nullable()
                  ->after('fecha_actuacion')
                  ->comment('Fecha estimada de la próxima actuación. Dispara notificación adicional.');
        });
    }

    public function down(): void
    {
        Schema::table('actuaciones_juicio', function (Blueprint $table) {
            $table->dropColumn('fecha_proxima_actuacion');
        });

        Schema::table('actuaciones_notaria', function (Blueprint $table) {
            $table->dropColumn('fecha_proxima_actuacion');
        });
    }
};
