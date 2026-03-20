<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abogado_seguimiento_juicio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seguimiento_juicio_id')
                  ->constrained('seguimientos_juicio')
                  ->cascadeOnDelete();

            // FK a users — los abogados son usuarios con rol 'abogado'
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Orden de asignación (1 = abogado principal)
            $table->unsignedTinyInteger('orden')->default(1)
                  ->comment('1 = principal, 2 = secundario, 3 = terciario. Máx 3 por juicio.');

            $table->timestamps();

            // Un mismo abogado no puede estar asignado 2 veces al mismo juicio
            $table->unique(['seguimiento_juicio_id', 'user_id']);

            // Máximo 3 abogados por juicio se valida a nivel de aplicación
            $table->index('seguimiento_juicio_id');
        });

        // NOTA: El campo 'abogado_nombre' (texto libre) en seguimientos_juicio
        // se mantiene temporalmente para preservar los abogados ya registrados
        // como texto libre. Se deprecará en una migración futura una vez que
        // todos los registros existan como usuarios en el sistema.
    }

    public function down(): void
    {
        Schema::dropIfExists('abogado_seguimiento_juicio');
    }
};
