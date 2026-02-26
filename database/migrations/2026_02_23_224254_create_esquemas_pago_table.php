<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esquemas_pago', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propiedad_id')
                ->constrained('propiedades')
                ->cascadeOnDelete()
                ->comment('Propiedad a la que pertenece este esquema');

            $table->decimal('apartado_monto', 12, 2)
                ->default(10000.00)
                ->comment('Monto del apartado que se descuenta del último pago');

            $table->decimal('total_porcentaje', 5, 2)
                ->default(0)
                ->comment('Suma total de porcentajes (debe ser 100)');

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que creó el esquema');

            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que modificó el esquema');

            $table->timestamps();

            // Solo un esquema por propiedad
            $table->unique('propiedad_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esquemas_pago');
    }
};
