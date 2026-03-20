<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->string('estrategia_juridica_archivo', 500)
                  ->nullable()
                  ->after('estrategia_juridica')
                  ->comment('Archivo de estrategia jurídica en disco private. Reemplaza a estrategia_juridica (texto) que queda deprecated.');
        });
    }

    public function down(): void
    {
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->dropColumn('estrategia_juridica_archivo');
        });
    }
};
