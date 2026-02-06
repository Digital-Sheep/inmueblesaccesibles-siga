<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->decimal('precio_valor_comercial', 12, 2)
                ->nullable()
                ->after('precio_lista')
                ->comment('Valor comercial de mercado (manual) para comparativo de remate');
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('precio_valor_comercial');
        });
    }
};
