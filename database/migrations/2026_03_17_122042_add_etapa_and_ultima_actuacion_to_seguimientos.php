<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar etapa_actual a actuaciones de juicio
        Schema::table('actuaciones_juicio', function (Blueprint $table) {
            $table->text('etapa_actual')->nullable()->after('descripcion_actuacion')
                  ->comment('Si se llena, el Observer propaga este valor a seguimientos_juicio.etapa_actual');
        });

        // Agregar etapa_actual a actuaciones de notaría
        Schema::table('actuaciones_notaria', function (Blueprint $table) {
            $table->text('etapa_actual')->nullable()->after('descripcion_actuacion')
                  ->comment('Si se llena, el Observer propaga este valor a seguimientos_notaria.etapa_actual');
        });

        // Agregar ultima_actuacion_at a seguimientos para ordenar/filtrar eficientemente
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->timestamp('ultima_actuacion_at')->nullable()->after('activo')
                  ->comment('Se actualiza automáticamente por el Observer al crear una actuación');
        });

        Schema::table('seguimientos_notaria', function (Blueprint $table) {
            $table->timestamp('ultima_actuacion_at')->nullable()->after('activo')
                  ->comment('Se actualiza automáticamente por el Observer al crear una actuación');
        });
    }

    public function down(): void
    {
        Schema::table('actuaciones_juicio', function (Blueprint $table) {
            $table->dropColumn('etapa_actual');
        });

        Schema::table('actuaciones_notaria', function (Blueprint $table) {
            $table->dropColumn('etapa_actual');
        });

        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->dropColumn('ultima_actuacion_at');
        });

        Schema::table('seguimientos_notaria', function (Blueprint $table) {
            $table->dropColumn('ultima_actuacion_at');
        });
    }
};
