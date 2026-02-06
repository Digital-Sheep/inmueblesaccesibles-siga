<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_tabulador_costos', function (Blueprint $table) {
            $table->id();
            $table->enum('tamano_propiedad', ['CHICA', 'MEDIANA', 'GRANDE', 'MUY_GRANDE']);

            $table->decimal('costo_remodelacion', 12, 2)->default(0);
            $table->decimal('costo_luz', 12, 2)->default(0);
            $table->decimal('costo_agua', 12, 2)->default(0);
            $table->decimal('costo_predial', 12, 2)->default(0);
            $table->decimal('costo_gastos_juridicos', 12, 2)->default(0)
                ->comment('Incluye gastos de juicio + notariales');

            $table->boolean('activo')->default(true);

            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que modificó los costos');

            $table->timestamps();

            $table->unique('tamano_propiedad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_tabulador_costos');
    }
};
