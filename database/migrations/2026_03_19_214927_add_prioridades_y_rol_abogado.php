<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar MEDIA y BAJA al enum de nivel_prioridad en seguimientos_juicio
        DB::statement("ALTER TABLE seguimientos_juicio MODIFY COLUMN nivel_prioridad
            ENUM('PRIORIDAD_ALTA','MEDIA','BAJA','REVISADO','SIN_REVISAR','NULO_NO_LITIGABLE')
            DEFAULT 'SIN_REVISAR'
            COMMENT 'NivelPrioridadJuicioEnum'");

        // Crear el rol 'abogado' en Spatie si no existe
        // Usamos insertOrIgnore para no fallar si ya existe
        $guardName = 'web';

        DB::table('roles')->insertOrIgnore([
            'name'       => 'abogado',
            'guard_name' => $guardName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Revertir enum sin MEDIA y BAJA
        DB::statement("ALTER TABLE seguimientos_juicio MODIFY COLUMN nivel_prioridad
            ENUM('PRIORIDAD_ALTA','REVISADO','SIN_REVISAR','NULO_NO_LITIGABLE')
            DEFAULT 'SIN_REVISAR'");

        // No eliminamos el rol en down() para evitar romper asignaciones existentes
    }
};
