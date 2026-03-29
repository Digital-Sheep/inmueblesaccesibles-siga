<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archivos', function (Blueprint $table) {
            // Nullable — los archivos existentes (propiedades, clientes, etc.)
            // conservan cat_carpeta_id = NULL y siguen funcionando igual.
            // Solo los documentos jurídicos usan esta FK.
            $table->foreignId('cat_carpeta_id')
                ->nullable()
                ->after('categoria')
                ->constrained('cat_carpetas_juridicas')
                ->nullOnDelete()
                ->comment('FK al catálogo de carpetas jurídicas. NULL = archivo de otro módulo.');
        });
    }

    public function down(): void
    {
        Schema::table('archivos', function (Blueprint $table) {
            $table->dropForeign(['cat_carpeta_id']);
            $table->dropColumn('cat_carpeta_id');
        });
    }
};
