<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            // Nuevo campo con lógica correcta: activo = hay demanda presentada
            $table->boolean('con_demanda')
                  ->default(false)
                  ->after('sin_demanda')
                  ->comment('Reemplaza sin_demanda. true = hay demanda presentada.');
        });

        // Migrar datos existentes:
        // sin_demanda = true  → con_demanda = false (no hay demanda)
        // sin_demanda = false → con_demanda = true  (sí hay demanda, era el caso normal)
        // IMPORTANTE: solo registros donde sin_demanda tiene valor explícito
        DB::statement('UPDATE seguimientos_juicio SET con_demanda = NOT sin_demanda');

        // El campo sin_demanda se mantiene como deprecated — no se elimina
        // para no perder contexto histórico ni romper código aún no migrado.
        // Marcar con comment para que quede claro en BD:
        DB::statement("ALTER TABLE seguimientos_juicio MODIFY COLUMN sin_demanda
            TINYINT(1) NOT NULL DEFAULT 0
            COMMENT 'DEPRECATED: usar con_demanda. Mantenido por compatibilidad.'");
    }

    public function down(): void
    {
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->dropColumn('con_demanda');
        });

        // Restaurar comment original
        DB::statement("ALTER TABLE seguimientos_juicio MODIFY COLUMN sin_demanda
            TINYINT(1) NOT NULL DEFAULT 0");
    }
};
