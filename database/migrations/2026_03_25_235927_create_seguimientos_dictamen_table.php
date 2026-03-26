<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos_dictamen', function (Blueprint $table) {
            $table->id();

            // ── Vinculación (opcional) ─────────────────────────────────────────
            $table->foreignId('propiedad_id')
                  ->nullable()
                  ->constrained('propiedades')
                  ->nullOnDelete();

            $table->foreignId('cliente_id')
                  ->nullable()
                  ->constrained('clientes')
                  ->nullOnDelete();

            // ── Identificación ────────────────────────────────────────────────
            $table->string('numero_credito', 100)->nullable()
                  ->comment('Auto desde propiedad o manual');

            $table->string('tipo_proceso', 20)
                  ->comment('TipoProcesoDict amenEnum: VENTA|CAMBIO|INVERSION');

            $table->foreignId('solicitante_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Usuario que solicita el dictamen');

            $table->foreignId('administradora_id')
                  ->nullable()
                  ->constrained('cat_administradoras')
                  ->nullOnDelete();

            $table->string('numero_juicio', 200)->nullable();
            $table->string('numero_expediente', 200)->nullable();
            $table->string('jurisdiccion', 200)->nullable();
            $table->string('via_procesal', 100)->nullable();

            // ── Dirección (auto desde propiedad o manual) ──────────────────────
            $table->text('direccion')->nullable();
            $table->string('estado_garantia', 100)->nullable();

            // ── Dictamen Jurídico ──────────────────────────────────────────────
            $table->string('dictamen_juridico_archivo', 500)->nullable()
                  ->comment('Disco private: juridico/dictamenes/{id}/juridico/');

            $table->string('dictamen_juridico_resultado', 20)->nullable()
                  ->comment('ResultadoDictamenEnum: POSITIVO|NEGATIVO|EN_ESPERA');

            $table->string('disponibilidad', 200)->nullable();

            // ── Carta de intención (solo cambios) ──────────────────────────────
            $table->string('carta_intencion_archivo', 500)->nullable()
                  ->comment('Solo aplica en tipo_proceso = CAMBIO');

            // ── Cofinavit ──────────────────────────────────────────────────────
            $table->boolean('tiene_cofinavit')->default(false);
            $table->decimal('valor_cofinavit', 15, 2)->nullable();

            // ── Dictamen Registral ─────────────────────────────────────────────
            $table->string('dictamen_registral_archivo', 500)->nullable()
                  ->comment('Disco private: juridico/dictamenes/{id}/registral/');

            $table->string('dictamen_registral_resultado', 20)->nullable()
                  ->comment('ResultadoDictamenEnum: POSITIVO|NEGATIVO|EN_ESPERA');

            // ── Valores ────────────────────────────────────────────────────────
            $table->decimal('valor_garantia', 15, 2)->nullable();
            $table->decimal('valor_catastral', 15, 2)->nullable()
                  ->comment('Siempre manual — el sistema no lo tiene');

            $table->decimal('valor_comercial_aproximado', 15, 2)->nullable();

            $table->decimal('valor_venta', 15, 2)->nullable()
                  ->comment('Auto desde cotización activa si hay propiedad, sino manual');

            $table->decimal('valor_sin_remodelacion', 15, 2)->nullable()
                  ->comment('Auto desde cotización activa si hay propiedad, sino manual');

            // ── Seguimiento ────────────────────────────────────────────────────
            $table->string('estatus', 20)->default('ACTIVO')
                  ->comment('EstatusDictamenEnum: ACTIVO|COMPLETADO');

            $table->text('etapa_actual')->nullable()
                  ->comment('Se propaga automáticamente desde la última actuación');

            $table->text('notas')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamp('ultima_actuacion_at')->useCurrent()
                  ->comment('Inicia con created_at. Observer lo actualiza al crear actuaciones.');

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ────────────────────────────────────────────────────────
            $table->index('tipo_proceso');
            $table->index('estatus');
            $table->index('activo');
            $table->index('numero_credito');
            $table->index('dictamen_juridico_resultado');
            $table->index('dictamen_registral_resultado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos_dictamen');
    }
};
