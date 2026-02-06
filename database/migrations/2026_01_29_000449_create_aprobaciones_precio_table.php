<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aprobaciones_precio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propiedad_id')
                ->constrained('propiedades')
                ->cascadeOnDelete();

            $table->foreignId('cotizacion_id')
                ->constrained('cotizaciones')
                ->cascadeOnDelete()
                ->comment('Cotización que se está aprobando');

            $table->decimal('precio_propuesto', 12, 2)
                ->comment('El precio que se está evaluando');

            $table->enum('tipo_aprobador', ['COMERCIAL', 'CONTABILIDAD', 'DGE'])
                ->comment('Área que debe aprobar');

            $table->enum('estatus', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])
                ->default('PENDIENTE');

            $table->decimal('precio_sugerido_alternativo', 12, 2)
                ->nullable()
                ->comment('Si rechazan, pueden sugerir otro precio');

            $table->text('comentarios')->nullable()
                ->comment('Retroalimentación del aprobador');

            $table->foreignId('aprobador_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que aprueba/rechaza');

            $table->timestamp('fecha_respuesta')->nullable();

            $table->timestamps();

            // Índice para consultas rápidas
            $table->index(['propiedad_id', 'tipo_aprobador', 'estatus']);
            $table->index(['cotizacion_id', 'tipo_aprobador']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aprobaciones_precio');
    }
};
