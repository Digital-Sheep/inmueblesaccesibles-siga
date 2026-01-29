<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->string('nombre_acreditado', 255)
                ->nullable()
                ->after('numero_credito')
                ->comment('Nombre del acreditado/deudor original - DATO SENSIBLE');
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('nombre_acreditado');
        });
    }
};

