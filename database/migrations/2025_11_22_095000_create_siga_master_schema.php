<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // 1. CATÁLOGOS DEL SISTEMA
        // ==========================================

        Schema::create('cat_estados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // "Jalisco"
            $table->string('abreviatura', 10)->nullable(); // "JAL"

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cat_municipios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_id')->constrained('cat_estados');
            $table->string('nombre'); // "Guadalajara"

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cat_sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Mazatlán, Culiacán
            $table->string('abreviatura', 10)->nullable();
            $table->boolean('activo')->default(true);

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cat_administradoras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Zendere, BBVA
            $table->string('abreviatura', 10)->nullable();
            $table->string('contacto_principal')->nullable();
            $table->boolean('activo')->default(true);

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cat_tipos_juicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Especial Hipotecario"
            $table->boolean('activo')->default(true);

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cat_etapas_procesales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Presentación de Demanda

            // Relación: Una etapa pertenece a un tipo de juicio específico
            // (O se deja NULL si es una etapa genérica para todos)
            $table->foreignId('tipo_juicio_id')->nullable()->constrained('cat_tipos_juicios');
            $table->integer('dias_termino_legal')->default(0);
            $table->integer('orden')->default(0);

            $table->boolean('activo')->default(true);

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ==========================================
        // 2. MÓDULO COMERCIAL
        // ==========================================

        Schema::create('carteras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Lote Mayo 2025"
            $table->foreignId('administradora_id')->constrained('cat_administradoras');
            $table->string('archivo_path')->nullable();
            $table->date('fecha_recepcion');
            $table->enum('estatus', ['BORRADOR', 'VALIDADA', 'PUBLICADA'])->default('BORRADOR');

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            // Personales
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('nombre_completo_virtual')->virtualAs('concat(nombres, " ", apellido_paterno, " ", ifnull(apellido_materno, ""))');

            // Contacto
            $table->string('email')->nullable();
            $table->string('celular', 20);

            // Fiscales
            $table->string('rfc', 13)->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('estado_civil')->nullable();
            $table->text('direccion_fiscal')->nullable();

            // Gestión
            $table->foreignId('sucursal_id')->constrained('cat_sucursales');
            $table->foreignId('usuario_responsable_id')->constrained('users'); // El dueño del cliente

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prospectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('celular', 20);
            $table->string('email')->nullable();
            $table->string('origen')->nullable();

            $table->enum('estatus', ['NUEVO', 'CONTACTADO', 'CITA', 'APARTADO', 'CLIENTE', 'DESCARTADO'])->default('NUEVO');
            $table->string('motivo_descarte')->nullable();

            $table->foreignId('sucursal_id')->constrained('cat_sucursales');
            $table->foreignId('usuario_responsable_id')->constrained('users');
            $table->foreignId('convertido_a_cliente_id')->nullable()->constrained('clientes');

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('numero_credito')->nullable()->index();
            $table->foreignId('cartera_id')->nullable()->constrained('carteras')->nullOnDelete();
            $table->foreignId('sucursal_id')->constrained('cat_sucursales');
            $table->foreignId('administradora_id')->nullable()->constrained('cat_administradoras');

            // Ubicación
            $table->text('direccion_completa');
            $table->string('calle')->nullable();
            $table->string('numero_exterior')->nullable();
            $table->string('numero_interior')->nullable();
            $table->string('colonia')->nullable();
            $table->string('fraccionamiento')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->foreignId('estado_id')->nullable()->constrained('cat_estados');
            $table->foreignId('municipio_id')->nullable()->constrained('cat_municipios');

            $table->string('estado_borrador')->nullable();
            $table->string('municipio_borrador')->nullable();

            // Geolocalización
            $table->text('google_maps_link')->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();

            // Características
            $table->string('tipo_vivienda')->nullable();
            $table->string('tipo_inmueble')->nullable();
            $table->decimal('terreno_m2', 10, 2)->nullable();
            $table->decimal('construccion_m2', 10, 2)->nullable();
            $table->integer('habitaciones')->nullable();
            $table->integer('banos')->nullable();
            $table->integer('estacionamientos')->nullable();

            // Datos Legales Reportados (Informativos)
            $table->string('etapa_judicial_reportada')->nullable();
            $table->decimal('avaluo_banco', 15, 2)->nullable();
            $table->decimal('cofinavit_monto', 15, 2)->nullable();

            // Valores
            $table->decimal('precio_lista', 15, 2)->nullable();
            $table->decimal('precio_venta_sugerido', 15, 2)->nullable();
            $table->decimal('precio_minimo', 15, 2)->nullable();

            // Estatus
            $table->enum('estatus_comercial', ['BORRADOR', 'EN_REVISION', 'DISPONIBLE', 'EN_PROCESO', 'VENDIDA', 'BAJA'])->default('BORRADOR');
            $table->enum('estatus_legal', ['SIN_REVISAR', 'R1_NEGATIVO', 'R2_POSITIVO', 'ADJUDICADA', 'ESCRITURADA'])->default('SIN_REVISAR');

            $table->nullableMorphs('interesado_principal', 'idx_prop_interesado');

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('procesos_venta', function (Blueprint $table) {
            $table->id();

            // Relación Polimórfica: ¿Quién compra? (Prospecto o Cliente)
            // Crea: interesado_type, interesado_id
            $table->morphs('interesado');

            $table->foreignId('propiedad_id')->constrained('propiedades');

            $table->foreignId('vendedor_id')->constrained('users');

            // Flujo del Proceso
            $table->enum('estatus', [
                'ACTIVO',           // En negociación / Visitas
                'VISITA_REALIZADA', // Visitó la propiedad
                'APARTADO_GENERADO', // Se generó el contrato de apartado de 10k
                'APARTADO_POR_VALIDAR', // Comprobante de pago cargado, esperando validación
                'EN_DICTAMINACION', // Jurídico trabajando
                'DICTAMINADO_R2',   // Luz verde para enganche
                'ESPERANDO_ENGANCHE', // Esperando el 50%
                'ENGANCHE_PAGADO',  // 50% validado -> Firma Contrato
                'CERRADO_GANADO',   // Venta finalizada
                'CANCELADO',        // Se cayó la venta
                'CAMBIO_PROPIEDAD'  // Se movió a otra casa (RV)
            ])->default('ACTIVO');

            $table->string('folio_apartado')->nullable(); // El folio que genera el sistema

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('eventos_agenda', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->enum('tipo', ['CITA_VISITA', 'LLAMADA', 'FIRMA_CONTRATO', 'REUNION_INTERNA']);

            $table->nullableMorphs('participante');

            $table->foreignId('usuario_id')->constrained('users');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('interacciones', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('entidad');

            $table->enum('tipo', ['LLAMADA', 'WHATSAPP', 'CORREO', 'VISITA_SUCURSAL', 'VISITA_PROPIEDAD', 'NOTA_INTERNA']);
            $table->enum('resultado', ['CONTACTADO', 'BUZON', 'CITA_AGENDADA', 'NO_INTERESA', 'SIN_RESPUESTA'])->nullable();

            $table->text('comentario'); // Detalle de la interacción
            $table->dateTime('fecha_programada')->nullable(); // Si es una cita futura
            $table->dateTime('fecha_realizada')->nullable();  // Cuándo ocurrió

            $table->foreignId('usuario_id')->constrained('users');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });


        // ==========================================
        // 3. MÓDULO JURÍDICO
        // ==========================================

        Schema::create('dictamenes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_solicitud', ['VENTA', 'CAMBIO', 'INVERSION']);
            $table->enum('origen_solicitud', ['CARTERA', 'EXTERNO', 'CLIENTE_R1'])->default('CARTERA');

            $table->foreignId('proceso_venta_id')->nullable()->constrained('procesos_venta');
            $table->foreignId('propiedad_id')->constrained('propiedades');

            $table->foreignId('usuario_solicitante_id')->constrained('users');

            // Datos del Excel
            $table->text('direccion_completa')->nullable();
            $table->string('numero_credito')->nullable();
            $table->string('numero_credito_anterior')->nullable();
            $table->string('nombre_proveedor')->nullable();

            $table->boolean('es_dueno_real')->default(false);
            $table->boolean('tiene_posesion')->default(false);
            $table->date('fecha_ultimo_pago_deudor')->nullable();

            $table->enum('estatus', ['PENDIENTE', 'EN_REVISION', 'TERMINADO'])->default('PENDIENTE');
            $table->enum('resultado_final', ['POSITIVO', 'NEGATIVO', 'CAMBIO'])->nullable();
            $table->string('nomenclatura_generada')->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expedientes_juridicos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_expediente')->unique();
            $table->foreignId('dictamen_origen_id')->constrained('dictamenes');
            $table->foreignId('proceso_venta_id')->nullable()->constrained('procesos_venta');

            $table->enum('etapa_global', ['PREVIO', 'LITIGIO', 'ADJUDICACION', 'ESCRITURACION', 'ENTREGADO'])->default('PREVIO');
            $table->foreignId('abogado_responsable_id')->constrained('users');

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('juicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes_juridicos');
            $table->foreignId('tipo_juicio_id')->constrained('cat_tipos_juicios');

            $table->string('no_expediente_juzgado');
            $table->string('juzgado');
            $table->string('distrito_judicial');

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('seguimientos_juridicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes_juridicos');
            $table->foreignId('etapa_id')->constrained('cat_etapas_procesales');

            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('estatus_semaforo', ['VERDE', 'AMARILLO', 'ROJO', 'COMPLETADO'])->default('VERDE');
            $table->text('observaciones')->nullable();
            $table->string('documento_evidencia_url')->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('solicitudes_contrato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_venta_id')->constrained('procesos_venta');

            $table->enum('tipo_contrato', ['APARTADO', 'PRESTACION_SERVICIOS', 'CESION_DERECHOS', 'PROMESA_COMPRA']);

            // Control de Versiones y Estatus
            $table->enum('estatus', ['SOLICITADO', 'BORRADOR_GENERADO', 'EN_REVISION_LEGAL', 'APROBADO', 'FIRMADO', 'CANCELADO'])->default('SOLICITADO');

            // Fechas clave
            $table->dateTime('fecha_solicitud');
            $table->dateTime('fecha_firma_programada')->nullable();
            $table->dateTime('fecha_firma_real')->nullable();

            // Responsables
            $table->foreignId('elaborado_por_id')->nullable()->constrained('users');
            $table->foreignId('aprobado_por_id')->nullable()->constrained('users');

            $table->text('notas_legales')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ==========================================
        // 4. MÓDULO FINANCIERO
        // ==========================================

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->enum('concepto', ['APARTADO', 'ENGANCHE', 'LIQUIDACION', 'ABONO']);
            $table->foreignId('proceso_venta_id')->nullable()->constrained('procesos_venta');

            $table->decimal('monto', 15, 2);
            $table->enum('metodo_pago', ['EFECTIVO', 'TRANSFERENCIA', 'CHEQUE']);
            $table->string('comprobante_url')->nullable();

            $table->enum('estatus', ['PENDIENTE', 'VALIDADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->foreignId('validado_por_id')->nullable()->constrained('users');
            $table->dateTime('fecha_validacion')->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ==========================================
        // 5. GESTIÓN DOCUMENTAL
        // ==========================================

        Schema::create('archivos', function (Blueprint $table) {
            $table->id();

            // Relación Polimórfica
            // Esto permite que un archivo pertenezca a un Cliente, una Propiedad o un Expediente
            // Crea automáticamente: 'entidad_type' y 'entidad_id'
            $table->morphs('entidad');

            // Clasificación
            $table->string('categoria')->index(); // Ej: 'INE', 'FOTO_FACHADA', 'SENTENCIA', 'COMPROBANTE'
            $table->string('nombre_original'); // Para que el usuario reconozca su archivo
            $table->string('ruta_archivo'); // La ubicación real en el disco
            $table->string('tipo_mime')->nullable(); // pdf, jpg, png
            $table->integer('peso_kb')->nullable();

            // Metadatos extra (Opcional)
            $table->text('descripcion')->nullable(); // Ej: "Foto dañada del baño", "INE vencida"
            $table->foreignId('subido_por_id')->constrained('users'); // Auditoría

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ==========================================
        // 6. NOTIFICACIONES Y VALIDACIONES
        // ==========================================

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // A quién se le avisa (User)
            $table->text('data'); // El mensaje JSON ("Tu pago fue validado")
            $table->timestamp('read_at')->nullable(); // ¿Ya la vio?
            $table->timestamps();
        });

        Schema::create('validaciones_proceso', function (Blueprint $table) {
            $table->id();

            // ¿Qué se está validando? (Polimórfico)
            // Ej: Un Dictamen (id 5), un Pago (id 20), una Solicitud de Contrato (id 8)
            $table->nullableMorphs('validable');

            // ¿Qué acción se intenta hacer?
            $table->string('accion_intentada'); // Ej: 'SOLICITAR_DICTAMEN', 'GENERAR_CONTRATO'

            // ¿Quién debe aprobar? (Rol o Usuario específico)
            $table->foreignId('rol_validador_id')->nullable()->constrained('roles');
            $table->foreignId('usuario_validador_id')->nullable()->constrained('users'); // Si es alguien específico

            // Estado de la validación
            $table->enum('estatus', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])->default('PENDIENTE');

            $table->text('comentarios')->nullable(); // "Rechazado porque falta la foto"
            $table->dateTime('fecha_resolucion')->nullable();

            // Quién solicitó
            $table->foreignId('solicitante_id')->constrained('users');

            $table->timestamps();
        });

        // ==========================================
        // 7. LIQUIDACIONES
        // ==========================================
        Schema::create('liquidaciones_judiciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes_juridicos');

            // Datos del Excel de Liquidación
            $table->decimal('suerte_principal', 15, 2); // Monto base
            $table->decimal('tasa_interes_anual', 5, 2); // % Anual
            $table->date('fecha_inicio_mora');
            $table->date('fecha_corte');
            $table->decimal('total_calculado', 15, 2)->nullable();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // ==========================================
        // 8. MODIFICACIÓN A USUARIOS
        // ==========================================

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->constrained('cat_sucursales');
        });
    }

    public function down(): void
    {
        // Borrar en orden inverso para no romper llaves foráneas
        Schema::dropIfExists('liquidaciones_judiciales');
        Schema::dropIfExists('validaciones_proceso');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('archivos');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('solicitudes_contrato');
        Schema::dropIfExists('seguimientos_juridicos');
        Schema::dropIfExists('juicios');
        Schema::dropIfExists('expedientes_juridicos');
        Schema::dropIfExists('dictamenes');
        Schema::dropIfExists('interacciones');
        Schema::dropIfExists('eventos_agenda');
        Schema::dropIfExists('procesos_venta');
        Schema::dropIfExists('propiedades');
        Schema::dropIfExists('prospectos');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('carteras');
        Schema::dropIfExists('cat_etapas_procesales');
        Schema::dropIfExists('cat_tipos_juicios');
        Schema::dropIfExists('cat_administradoras');
        Schema::dropIfExists('cat_sucursales');
        Schema::dropIfExists('cat_municipios');
        Schema::dropIfExists('cat_estados');
    }
};
