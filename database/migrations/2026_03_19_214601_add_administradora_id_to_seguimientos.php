<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            // FK nullable a cat_administradoras — nullable porque hay registros
            // existentes con texto libre en el campo 'administradora' que aún no
            // tienen correspondencia en el catálogo.
            $table->foreignId('administradora_id')
                ->nullable()
                ->after('administradora')
                ->constrained('cat_administradoras')
                ->nullOnDelete();
        });

        Schema::table('seguimientos_notaria', function (Blueprint $table) {
            $table->foreignId('administradora_id')
                ->nullable()
                ->after('administradora')
                ->constrained('cat_administradoras')
                ->nullOnDelete();
        });

        // NOTA: El campo 'administradora' (texto libre) se mantiene temporalmente
        // para preservar los valores históricos mientras se migran los registros
        // existentes al nuevo campo administradora_id. Una vez migrados todos los
        // registros, se puede deprecar en una migración futura.
    }

    public function down(): void
    {
        Schema::table('seguimientos_juicio', function (Blueprint $table) {
            $table->dropForeign(['administradora_id']);
            $table->dropColumn('administradora_id');
        });

        Schema::table('seguimientos_notaria', function (Blueprint $table) {
            $table->dropForeign(['administradora_id']);
            $table->dropColumn('administradora_id');
        });
    }
};
