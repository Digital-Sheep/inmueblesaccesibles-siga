<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dictamenes', function (Blueprint $table) {
            // Checkboxes de validación
            $table->boolean('direccion_correcta')->default(true);
            $table->boolean('credito_correcto')->default(true);
            $table->boolean('administradora_correcta')->default(true);

            // Campos para los datos reales (si los originales estaban mal)
            $table->text('direccion_corregida')->nullable();
            $table->string('numero_credito_corregido')->nullable();
            $table->foreignId('administradora_corregida_id')->nullable()->constrained('cat_administradoras');

            // Auditoría de este paso específico
            $table->dateTime('fecha_analisis_credito')->nullable();
            $table->foreignId('validado_por_director_id')->nullable()->constrained('users');
        });
    }
};
