<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE propiedades MODIFY COLUMN estatus_comercial ENUM(
            'BORRADOR',
            'EN_REVISION',
            'DISPONIBLE',
            'EN_INTERES',
            'EN_PROCESO',
            'VENDIDA',
            'BAJA'
        ) DEFAULT 'BORRADOR'");
    }

    public function down(): void
    {
        // Antes de revertir, regresar propiedades EN_INTERES a DISPONIBLE
        // para no romper el constraint del enum
        DB::statement("UPDATE propiedades SET estatus_comercial = 'DISPONIBLE' WHERE estatus_comercial = 'EN_INTERES'");

        DB::statement("ALTER TABLE propiedades MODIFY COLUMN estatus_comercial ENUM(
            'BORRADOR',
            'EN_REVISION',
            'DISPONIBLE',
            'EN_PROCESO',
            'VENDIDA',
            'BAJA'
        ) DEFAULT 'BORRADOR'");
    }
};
