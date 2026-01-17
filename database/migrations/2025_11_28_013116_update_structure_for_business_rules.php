<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. SEMÁFOROS Y ALERTAS (Para Catálogos)
        // Requerimiento: "Tienen 15 días para contestar", "Notificar a Dirección si vence".
        Schema::table('cat_etapas_procesales', function (Blueprint $table) {
            $table->integer('tiempo_maximo_dias')->default(0)->after('nombre'); // Días hábiles permitidos
            // Quién recibe la alerta si se vence
            $table->foreignId('rol_alerta_id')->nullable()->constrained('roles')->nullOnDelete();
        });

        // 2. DETALLE JURÍDICO FLEXIBLE
        // Requerimiento: Guardar campos locos del Excel (postura legal, almoneda, etc.) sin crear 20 tablas.
        Schema::table('seguimientos_juridicos', function (Blueprint $table) {
            $table->json('datos_etapa')->nullable()->after('observaciones');
            // Ej: {"postura_legal": 50000, "almoneda": "2da"}
        });

        // 3. PRODUCTO "AHORRO POR TU CASA" Y TIPOS DE VENTA
        // Requerimiento: Diferenciar venta directa de ahorro o inversión.
        Schema::table('procesos_venta', function (Blueprint $table) {
            $table->enum('tipo_proceso', ['VENTA_DIRECTA', 'AHORRO_CASA', 'INVERSION'])
                  ->default('VENTA_DIRECTA')
                  ->after('estatus');

            // Configuración del plan de ahorro (meta, plazo, mensualidad)
            $table->json('configuracion_plan')->nullable()->after('folio_apartado');

            // Contadores para la regla de "2 Llamadas Obligatorias"
            $table->integer('intentos_contacto_asesor')->default(0);
            $table->integer('intentos_contacto_gerente')->default(0);
            $table->enum('etapa_seguimiento', ['ASESOR', 'GERENTE_LOCAL', 'GERENTE_REMATES', 'CONCLUIDO'])
                  ->default('ASESOR');
        });

        // 4. VENTA CRUZADA (Bonos)
        // Requerimiento: Saber si la cita la agendó alguien diferente al dueño del prospecto.
        Schema::table('interacciones', function (Blueprint $table) {
            $table->boolean('es_venta_cruzada')->default(false)->after('resultado');
            // El dueño original del prospecto (para saber a quién apoyaste)
            $table->foreignId('propietario_original_id')->nullable()->constrained('users')->nullOnDelete();
        });

        // 5. AJUSTE A VALIDACIONES (Opcional pero recomendado)
        // Para saber si una validación es "Urgente" (Semáforo Rojo)
        Schema::table('validaciones_proceso', function (Blueprint $table) {
             $table->boolean('es_urgente')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('cat_etapas_procesales', function (Blueprint $table) {
            $table->dropForeign(['rol_alerta_id']);
            $table->dropColumn(['tiempo_maximo_dias', 'rol_alerta_id']);
        });

        Schema::table('seguimientos_juridicos', function (Blueprint $table) {
            $table->dropColumn('datos_etapa');
        });

        Schema::table('procesos_venta', function (Blueprint $table) {
            $table->dropColumn(['tipo_proceso', 'configuracion_plan', 'intentos_contacto_asesor', 'intentos_contacto_gerente', 'etapa_seguimiento']);
        });

        Schema::table('interacciones', function (Blueprint $table) {
            $table->dropForeign(['propietario_original_id']);
            $table->dropColumn(['es_venta_cruzada', 'propietario_original_id']);
        });

        Schema::table('validaciones_proceso', function (Blueprint $table) {
            $table->dropColumn('es_urgente');
        });
    }
};
