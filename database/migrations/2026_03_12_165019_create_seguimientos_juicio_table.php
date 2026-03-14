<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos_juicio', function (Blueprint $table) {
            $table->id();

            // ── Vinculación con SIGA (opcional en v1) ──────────────────────────
            // FK nullable: se vincula cuando la propiedad exista en el sistema.
            // El campo numero_credito sirve como puente para la migración futura.
            $table->foreignId('propiedad_id')
                ->nullable()
                ->constrained('propiedades')
                ->nullOnDelete();

            $table->string('numero_credito', 100)->nullable()
                ->comment('Texto libre — puente para vincular con propiedades.numero_credito en el futuro');

            // ── Identificación del juicio ──────────────────────────────────────
            $table->string('id_garantia', 100)->nullable()
                ->comment('Ej: GAR-974132099 — identificador interno de la empresa');
            $table->string('nombre_cliente', 200)->nullable();
            $table->string('administradora', 200)->nullable();
            $table->text('domicilio')->nullable();

            // ── Clasificación operativa ────────────────────────────────────────
            $table->string('sede', 30)
                ->comment('SedeJuicioEnum: MAZATLAN|GUADALAJARA|LA_PAZ|CDMX|CULIACAN');
            $table->string('nivel_prioridad', 30)->default('SIN_REVISAR')
                ->comment('NivelPrioridadJuicioEnum');
            $table->string('tipo_proceso', 20)->nullable()
                ->comment('TipoProcesoJuicioEnum: VENTA|CAMBIO|INVERSION');
            $table->string('abogado_nombre', 200)->nullable();

            // ── Partes del juicio ──────────────────────────────────────────────
            $table->string('actor', 300)->nullable();
            $table->string('demandado', 300)->nullable();

            // ── Datos del expediente ───────────────────────────────────────────
            $table->string('numero_expediente', 200)->nullable();
            $table->string('juzgado', 500)->nullable();
            $table->string('distrito_judicial', 200)->nullable();
            $table->string('tipo_juicio_materia', 200)->nullable();
            $table->string('via_procesal', 100)->nullable();

            // ── Cesión de derechos ─────────────────────────────────────────────
            $table->boolean('hay_cesion_derechos')->default(false);
            $table->string('cedente', 500)->nullable();
            $table->string('cesionario', 500)->nullable();

            // ── Seguimiento narrativo ──────────────────────────────────────────
            $table->text('etapa_actual')->nullable()
                ->comment('Descripción libre de la etapa procesal actual');
            $table->text('estrategia_juridica')->nullable();
            $table->text('notas_director')->nullable()
                ->comment('Columna del director — notas internas de Paola/DGE');

            // ── Flags especiales ───────────────────────────────────────────────
            $table->boolean('sin_demanda')->default(false)
                ->comment('Agrupa los casos de hoja "Sin Demanda Con Alta Prioridad" del Excel');
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ────────────────────────────────────────────────────────
            $table->index('sede');
            $table->index('nivel_prioridad');
            $table->index('sin_demanda');
            $table->index('activo');
            $table->index('numero_credito');
            $table->index('id_garantia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos_juicio');
    }
};
