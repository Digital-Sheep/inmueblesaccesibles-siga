<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar ultima_actuacion_at de nullable a useCurrent()
        // para que MySQL asigne CURRENT_TIMESTAMP al insertar automáticamente.
        // Esto elimina nulls sin necesitar Observer ni lógica en PHP.

        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->timestamp('ultima_actuacion_at')
                ->useCurrent()
                ->nullable(false)
                ->change();
        });

        Schema::table('seguimientos_notaria', function (Blueprint $table) {
            $table->timestamp('ultima_actuacion_at')
                ->useCurrent()
                ->nullable(false)
                ->change();
        });

        // Rellenar registros existentes que tengan null
        DB::statement('UPDATE seguimientos_juicio SET ultima_actuacion_at = created_at WHERE ultima_actuacion_at IS NULL');
        DB::statement('UPDATE seguimientos_notaria SET ultima_actuacion_at = created_at WHERE ultima_actuacion_at IS NULL');
    }

    public function down(): void
    {
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->timestamp('ultima_actuacion_at')->nullable()->change();
        });

        Schema::table('seguimientos_notaria', function (Blueprint $table) {
            $table->timestamp('ultima_actuacion_at')->nullable()->change();
        });
    }
};
