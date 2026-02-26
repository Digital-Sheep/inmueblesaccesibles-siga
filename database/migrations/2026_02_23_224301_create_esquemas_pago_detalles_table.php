<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esquemas_pago_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('esquema_pago_id')
                ->constrained('esquemas_pago')
                ->cascadeOnDelete()
                ->comment('Esquema de pago al que pertenece');

            $table->integer('numero_pago')
                ->unsigned()
                ->comment('Número de pago (1, 2, 3...)');

            $table->string('descripcion')
                ->comment('Descripción del pago (Ej: Estudio de garantía)');

            $table->decimal('porcentaje', 5, 2)
                ->comment('Porcentaje del total (Ej: 35.00)');

            $table->decimal('monto_calculado', 12, 2)
                ->nullable()
                ->comment('Monto calculado según el precio de venta');

            $table->integer('orden')
                ->unsigned()
                ->comment('Orden de despliegue');

            $table->timestamps();

            // Índice para ordenamiento
            $table->index(['esquema_pago_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esquemas_pago_detalles');
    }
};
