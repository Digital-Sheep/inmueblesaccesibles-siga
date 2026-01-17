<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procesos_compra', function (Blueprint $table) {
            $table->id();

            // Vínculos
            $table->foreignId('proceso_venta_id')->nullable()->constrained('procesos_venta'); // Puede ser nulo si es INVERSION pura
            $table->foreignId('propiedad_id')->constrained('propiedades');
            $table->foreignId('dictamen_id')->constrained('dictamenes'); // Nace de un dictamen positivo

            // Clasificación (R2, R3, Inversión)
            $table->string('tipo_compra');

            // Estatus del Flujo de Compra
            $table->enum('estatus', [
                'INICIADO',             // Se validó el enganche, hay que empezar trámites
                'SOLICITUD_PAGO_PROV',  // Se pidió dinero para pagar al banco
                'PAGADO_PROVEEDOR',     // Ya se pagó la cesión
                'EN_NOTARIA',           // En trámite de escrituración/cesión
                'FIRMADO_EXITOSO',      // Ya somos dueños legalmente
                'CANCELADO'
            ])->default('INICIADO');

            // Datos Financieros (Nuestros costos)
            $table->decimal('precio_compra_negociado', 15, 2)->nullable(); // En cuánto nos la dejó el banco
            $table->decimal('gastos_notariales_presupuesto', 15, 2)->nullable();

            // Fechas Críticas
            $table->date('fecha_pago_proveedor')->nullable();
            $table->date('fecha_firma_cesion')->nullable();

            // Datos Notariales (Simplificados para esta fase)
            $table->string('notaria_numero')->nullable();
            $table->string('notario_nombre')->nullable();
            $table->string('numero_escritura')->nullable();

            // Auditoría
            $table->foreignId('responsable_id')->constrained('users'); // Quién lleva el trámite (GAD)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesos_compra');
    }
};
