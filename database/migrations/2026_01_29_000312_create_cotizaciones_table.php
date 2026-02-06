<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propiedad_id')
                ->constrained('propiedades')
                ->cascadeOnDelete()
                ->comment('Propiedad a la que pertenece esta cotización');

            $table->integer('version')->unsigned()->default(1)
                ->comment('Versión de la cotización (1, 2, 3...)');

            $table->boolean('activa')->default(true)
                ->comment('Solo UNA cotización activa por propiedad');

            // Datos base usados para el cálculo
            $table->decimal('precio_base', 12, 2)
                ->comment('Precio base usado (normalmente precio_lista)');

            $table->enum('tamano_propiedad', ['CHICA', 'MEDIANA', 'GRANDE', 'MUY_GRANDE'])
                ->comment('Tamaño al momento del cálculo');

            $table->foreignId('etapa_procesal_id')
                ->constrained('cat_etapas_procesales')
                ->comment('Etapa procesal al momento del cálculo');

            // DESGLOSE DE COSTOS ESTIMADOS
            $table->decimal('costo_remodelacion', 12, 2)->default(0);
            $table->decimal('costo_luz', 12, 2)->default(0);
            $table->decimal('costo_agua', 12, 2)->default(0);
            $table->decimal('costo_predial', 12, 2)->default(0);
            $table->decimal('costo_gastos_juridicos', 12, 2)->default(0)
                ->comment('Gastos de juicio + notariales');

            $table->decimal('total_costos', 12, 2)
                ->comment('Suma de todos los costos');

            // INCREMENTO POR INVERSIÓN
            $table->decimal('porcentaje_inversion', 5, 2)
                ->comment('35%, 20% o 15% según etapa');

            $table->decimal('monto_inversion', 12, 2)
                ->comment('Monto calculado del incremento');

            // PRECIOS RESULTANTES (duplicados aquí para historial)
            $table->decimal('precio_sin_remodelacion', 12, 2)
                ->comment('Base + costos sin remodelación + inversión');

            $table->decimal('precio_venta_sugerido', 12, 2)
                ->comment('Base + todos los costos + inversión');

            $table->decimal('porcentaje_descuento', 5, 2)
                ->comment('% descuento aplicado en este cálculo');

            $table->decimal('precio_venta_con_descuento', 12, 2)
                ->comment('Precio final con descuento');

            $table->decimal('porcentaje_utilidad', 5, 2)
                ->comment('% utilidad esperada');

            // AUDITORÍA
            $table->foreignId('calculada_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que ejecutó el cálculo');

            $table->text('notas')->nullable()
                ->comment('Observaciones adicionales del cálculo');

            $table->timestamps();

            // Índices
            $table->index(['propiedad_id', 'activa']);
            $table->index(['propiedad_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};

