<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vincular Cartera con Sucursal (El "Padre" de las casas)
        Schema::table('carteras', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable() // Nulo por si es una cartera nacional/mixta
                ->after('administradora_id')
                ->constrained('cat_sucursales')
                ->nullOnDelete();
        });

        // 2. Campo extra para la fecha judicial del Excel
        Schema::table('propiedades', function (Blueprint $table) {
            $table->date('fecha_corte_judicial')
                ->nullable()
                ->after('etapa_judicial_reportada')
                ->comment('Fecha de ultima etapa judicial reportada en carga masiva');
        });
    }

    public function down(): void
    {
        Schema::table('carteras', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('fecha_corte_judicial');
        });
    }
};
