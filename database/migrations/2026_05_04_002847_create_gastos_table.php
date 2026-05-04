<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();

            // ── Polimórfico — a qué registro pertenece el gasto ───────────────
            // Hoy: SeguimientoJuicio, SeguimientoNotaria, SeguimientoDictamen
            // Mañana: cualquier modelo que necesite registrar gastos
            $table->morphs('gastable'); // gastable_type / gastable_id

            // ── Clasificación del documento ───────────────────────────────────
            $table->string('tipo_documento', 30)
                ->comment('TipoDocumentoGastoEnum: COMPROBANTE|FACTURA|RECIBO');

            // ── Detalle del gasto ─────────────────────────────────────────────
            $table->string('concepto', 200)
                ->comment('Descripción libre: Honorarios abogado, Gestoría RPPC, etc.');

            $table->decimal('monto', 15, 2);

            $table->string('metodo_pago', 20)
                ->comment('MetodoPagoGastoEnum: EFECTIVO|TRANSFERENCIA|CHEQUE');

            $table->date('fecha_pago');

            // ── Comprobante (opcional) ────────────────────────────────────────
            // Path en disco private siguiendo la misma convención de paths del proyecto
            // Ej: juridico/juicios/GAR-XXXX/gastos/1743000000_comprobante.pdf
            $table->string('comprobante_path', 500)->nullable()
                ->comment('Ruta en disco private. NULL si no se subió comprobante.');

            $table->string('comprobante_nombre_original', 300)->nullable()
                ->comment('Nombre original del archivo para mostrarlo en UI');

            $table->text('descripcion')->nullable()
                ->comment('Notas adicionales opcionales');

            // ── Auditoría ─────────────────────────────────────────────────────
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices ───────────────────────────────────────────────────────
            $table->index('fecha_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
