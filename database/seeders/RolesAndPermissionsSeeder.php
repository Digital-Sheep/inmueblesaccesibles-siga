<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caché
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Schema::disableForeignKeyConstraints();

        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        Schema::enableForeignKeyConstraints();

        // ==========================================
        // 2. DEFINIR PERMISOS (Granulares)
        // ==========================================
        $permisos = [
            // ==========================================
            // 🏠 BASE / SISTEMA
            // ==========================================
            'ver_panel_principal',
            'ver_actividad_sistema',

            // ==========================================
            // 🧭 NAVEGACIÓN / MENÚS (Visibilidad en Sidebar
            // ==========================================

            // Menús Principales
            'menu_comercial',
            'menu_juridico',
            'menu_administrativo',
            'menu_configuracion',

            // Submenús Comercial
            'menu_prospectos',
            'menu_clientes',
            'menu_propiedades',
            'menu_ventas',
            'menu_agenda',
            'menu_cartera',
            'menu_seguimiento',

            // Submenús Jurídico
            'menu_dictamenes',
            'menu_expedientes',
            'menu_juicios',
            'menu_seguimientos_juridicos',
            'menu_formalizacion',
            'menu_cambios',

            // Submenús Administrativo
            'menu_pagos',
            'menu_compras',
            'menu_contratos',
            'menu_devoluciones',
            'menu_validaciones',

            // Submenús Configuración
            'menu_usuarios',
            'menu_roles',
            'menu_catalogos',
            'menu_reportes',

            // Submenús Atención al Cliente
            'menu_atencion_cliente',
            'menu_interacciones',

            // ==========================================
            // 👥 USUARIOS Y ROLES
            // ==========================================
            'usuarios_ver',
            'usuarios_crear',
            'usuarios_editar',
            'usuarios_desactivar',
            'usuarios_asignar_rol',
            'usuarios_cambiar_sucursal',

            'roles_ver',
            'roles_crear',
            'roles_editar',
            'roles_eliminar',
            'roles_asignar_permisos',

            // ==========================================
            // 📊 DASHBOARDS Y REPORTES
            // ==========================================

            // Dashboards
            'dashboard_comercial',
            'dashboard_financiero',
            'dashboard_juridico',
            'dashboard_general',

            // Reportes
            'reportes_ventas',
            'reportes_cobranza',
            'reportes_cartera',
            'reportes_juridicos',
            'reportes_personalizados',

            // ==========================================
            // 🎯 MÓDULO: PROSPECTOS
            // ==========================================
            'prospectos_ver',
            'prospectos_crear',
            'prospectos_editar',
            'prospectos_eliminar',
            'prospectos_exportar',
            'prospectos_asignar',
            'prospectos_reasignar',
            'prospectos_importar',
            'prospectos_ver_todos',
            'prospectos_ver_sucursal_completa',

            // ==========================================
            // 👤 MÓDULO: CLIENTES
            // ==========================================
            'clientes_ver',
            'clientes_crear',
            'clientes_editar',
            'clientes_eliminar',
            'clientes_exportar',
            'clientes_ver_todos',
            'clientes_ver_expediente',
            'clientes_editar_expediente',
            'clientes_validar_expediente',
            'clientes_historial_completo',
            'clientes_ver_sucursal_completa',

            // ==========================================
            // 🏘️ MÓDULO: PROPIEDADES / GARANTÍAS
            // ==========================================
            'propiedades_ver',
            'propiedades_crear',
            'propiedades_editar',
            'propiedades_eliminar',
            'propiedades_exportar',
            'propiedades_asignar_cartera',
            'propiedades_cambiar_estatus',
            'propiedades_subir_fotos',
            'propiedades_ver_todos',
            'propiedades_ver_historial',

            'carteras_ver',
            'carteras_crear',
            'carteras_editar',
            'carteras_descargar',

            // ==========================================
            // 💼 MÓDULO: PROCESOS DE VENTA (SVT)
            // ==========================================
            'ventas_ver',
            'ventas_crear',
            'ventas_editar',
            'ventas_cancelar',
            'ventas_ver_todas',
            'ventas_ver_sucursal_completa',
            'ventas_ver_historial',
            'ventas_agregar_nota',
            'ventas_exportar',

            // Apartados
            'ventas_registrar_apartado',
            'ventas_validar_apartado',
            'ventas_aprobar_apartado',

            // Contratos
            'ventas_solicitar_contrato',
            'ventas_subir_contrato',
            'ventas_validar_contrato',
            'ventas_aprobar_contrato',

            // Pagos
            'ventas_registrar_pago',
            'ventas_validar_pago',

            // ==========================================
            // ⚖️ MÓDULO: DICTÁMENES
            // ==========================================
            'dictamenes_ver',
            'dictamenes_crear',
            'dictamenes_editar',
            'dictamenes_eliminar',
            'dictamenes_exportar',
            'dictamenes_ver_todos',

            // Flujo de Trabajo
            'dictamenes_solicitar',
            'dictamenes_asignar',
            'dictamenes_elaborar',
            'dictamenes_revisar',
            'dictamenes_aprobar',
            'dictamenes_rechazar',
            'dictamenes_cambiar_nomenclatura',

            // ==========================================
            // 🏦 MÓDULO: PROCESOS DE COMPRA
            // ==========================================
            'compras_ver',
            'compras_crear',
            'compras_editar',
            'compras_cancelar',
            'compras_ver_todas',
            'compras_ver_historial',

            // Flujo GAD
            'compras_solicitar',
            'compras_negociar_precio',
            'compras_registrar_pago_proveedor',
            'compras_validar_pago',
            'compras_subir_documentos',

            // Notaría
            'compras_asignar_notaria',
            'compras_registrar_escritura',
            'compras_finalizar',

            // ==========================================
            // 📂 MÓDULO: EXPEDIENTES JURÍDICOS
            // ==========================================
            'expedientes_ver',
            'expedientes_crear',
            'expedientes_editar',
            'expedientes_eliminar',
            'expedientes_ver_todos',

            // Gestión Documental
            'expedientes_subir_documentos',
            'expedientes_descargar_documentos',
            'expedientes_validar_documentos',

            // Seguimiento
            'expedientes_agregar_seguimiento',
            'expedientes_cambiar_etapa',

            // ==========================================
            // 🏛️ MÓDULO: JUICIOS / LITIGIO (UCM)
            // ==========================================
            'juicios_ver',
            'juicios_crear',
            'juicios_editar',
            'juicios_archivar',
            'juicios_exportar',
            'juicios_ver_todos',

            // Seguimiento Judicial
            'juicios_agregar_actuacion',
            'juicios_subir_promocion',
            'juicios_registrar_audiencia',
            'juicios_cambiar_etapa',
            'juicios_asignar_abogado',

            // ==========================================
            // 💰 MÓDULO: PAGOS
            // ==========================================
            'pagos_ver',
            'pagos_crear',
            'pagos_editar',
            'pagos_eliminar',
            'pagos_exportar',
            'pagos_ver_todos',
            'pagos_ver_dashboard',

            // Validación (PCA)
            'pagos_validar_ingreso',
            'pagos_rechazar_ingreso',
            'pagos_validar_egreso',
            'pagos_conciliar',

            // ==========================================
            // 📄 MÓDULO: SOLICITUDES DE CONTRATO
            // ==========================================
            'contratos_ver',
            'contratos_crear',
            'contratos_editar',
            'contratos_cancelar',
            'contratos_ver_todos',
            'contratos_ver_historial',

            // Flujo UFC
            'contratos_elaborar_minuta',
            'contratos_enviar_notaria',
            'contratos_registrar_firma',
            'contratos_subir_firmado',
            'contratos_entregar_cliente',

            // ==========================================
            // ✅ MÓDULO: VALIDACIONES DE PROCESO
            // ==========================================
            'validaciones_ver',
            'validaciones_aprobar',
            'validaciones_rechazar',
            'validaciones_ver_historial',
            'validaciones_ver_todas',

            // ==========================================
            // 💬 MÓDULO: INTERACCIONES / SEGUIMIENTO
            // ==========================================
            'interacciones_ver',
            'interacciones_crear',
            'interacciones_editar',
            'interacciones_eliminar',
            'interacciones_ver_todas',
            'interacciones_exportar',
            'interacciones_ver_sucursal_completa',

            // ==========================================
            // 📅 MÓDULO: EVENTOS / AGENDA
            // ==========================================
            'agenda_ver',
            'agenda_crear',
            'agenda_editar',
            'agenda_eliminar',
            'agenda_ver_todos',
            'agenda_asignar_participantes',
            'agenda_ver_sucursal_completa',

            // ==========================================
            // 📎 MÓDULO: ARCHIVOS / DOCUMENTOS
            // ==========================================
            'archivos_ver',
            'archivos_subir',
            'archivos_descargar',
            'archivos_eliminar',
            'archivos_ver_todos',

            // ==========================================
            // 💸 MÓDULO: DEVOLUCIONES
            // ==========================================
            'devoluciones_ver',
            'devoluciones_crear',
            'devoluciones_ver_todas',

            // Flujo de Aprobación
            'devoluciones_validar_admin',
            'devoluciones_validar_juridico',
            'devoluciones_aprobar_direccion',
            'devoluciones_ejecutar',

            // ==========================================
            // 📝 MÓDULO: FORMALIZACIÓN / NOTARÍAS (UFC)
            // ==========================================
            'formalizacion_ver',
            'formalizacion_crear',
            'formalizacion_elaborar_minuta',
            'formalizacion_enviar_notaria',
            'formalizacion_registrar_escritura',
            'formalizacion_entregar',
            'formalizacion_ver_todas',

            // ==========================================
            // 🔄 MÓDULO: CAMBIOS DE GARANTÍA (URRJ)
            // ==========================================
            'cambios_ver',
            'cambios_crear',
            'cambios_evaluar',
            'cambios_dictaminar',
            'cambios_aprobar',
            'cambios_ejecutar',
            'cambios_ver_todos',

            // ==========================================
            // 🗂️ MÓDULO: CATÁLOGOS
            // ==========================================
            'catalogos_sucursales',
            'catalogos_administradoras',
            'catalogos_tipos_juicio',
            'catalogos_etapas_procesales',
            'catalogos_estados_municipios',

            // ==========================================
            // 🎛️ MÓDULO: CONFIGURACIÓN
            // ==========================================
            'configuracion_ver',
            'configuracion_editar',
            'configuracion_sistema',

            // ==========================================
            // 📞 MÓDULO: ATENCIÓN AL CLIENTE (UAC/RAC)
            // ==========================================
            'atencion_ver_casos',
            'atencion_crear_caso',
            'atencion_asignar_caso',
            'atencion_resolver_caso',
            'atencion_ver_todos',

            // ==========================================
            // 🔐 PERMISOS ESPECIALES / DESCUENTOS
            // ==========================================
            'autorizar_descuentos_bajos',
            'autorizar_descuentos_medios',
            'autorizar_descuentos_altos',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // ==========================================
        // 3. CREACIÓN DE ROLES
        // ==========================================

        // --- NIVEL 1: DIRECTORES ESTRATÉGICOS ---

        // 🆕 DIRECCIÓN GENERAL EJECUTIVA (DGE)
        $role = Role::firstOrCreate(['name' => 'DGE']);
        $role->syncPermissions([
            // 🏠 BASE
            'ver_panel_principal',
            'ver_actividad_sistema',

            // 🧭 NAVEGACIÓN - VE TODO
            'menu_comercial',
            'menu_juridico',
            'menu_administrativo',
            'menu_configuracion',
            'menu_prospectos',
            'menu_clientes',
            'menu_propiedades',
            'menu_ventas',
            'menu_agenda',
            'menu_dictamenes',
            'menu_expedientes',
            'menu_juicios',
            'menu_seguimientos_juridicos',
            'menu_formalizacion',
            'menu_cambios',
            'menu_pagos',
            'menu_compras',
            'menu_contratos',
            'menu_devoluciones',
            'menu_validaciones',
            'menu_usuarios',
            'menu_roles',
            'menu_catalogos',
            'menu_reportes',
            'menu_atencion_cliente',
            'menu_interacciones',

            // 📊 DASHBOARDS - TODOS
            'dashboard_comercial',
            'dashboard_financiero',
            'dashboard_juridico',
            'dashboard_general',

            // 📈 REPORTES - TODOS
            'reportes_ventas',
            'reportes_cobranza',
            'reportes_cartera',
            'reportes_juridicos',
            'reportes_personalizados',

            // 👥 GESTIÓN DE USUARIOS
            'usuarios_ver',
            'usuarios_crear',
            'usuarios_editar',
            'usuarios_desactivar',
            'usuarios_asignar_rol',
            'usuarios_cambiar_sucursal',

            // 🔐 GESTIÓN DE ROLES
            'roles_ver',
            'roles_crear',
            'roles_editar',
            'roles_asignar_permisos',

            // 🎯 PROSPECTOS - SOLO LECTURA GENERAL
            'prospectos_ver',
            'prospectos_ver_todos',
            'prospectos_exportar',

            // 👤 CLIENTES - LECTURA COMPLETA
            'clientes_ver',
            'clientes_ver_todos',
            'clientes_ver_expediente',
            'clientes_historial_completo',
            'clientes_exportar',

            // 🏘️ PROPIEDADES / CARTERAS - ASIGNACIÓN Y SUPERVISIÓN
            'propiedades_ver',
            'propiedades_crear',              // 🔑 RECIBE CARTERA DE ADMINISTRADORAS
            'propiedades_editar',
            'propiedades_exportar',
            'propiedades_cambiar_estatus',
            'propiedades_ver_historial',

            // CARTERAS
            'carteras_ver',
            'carteras_crear',
            'carteras_editar',
            'carteras_descargar',

            // 💼 VENTAS - SUPERVISIÓN GENERAL
            'ventas_ver',
            'ventas_ver_todas',
            'ventas_ver_historial',
            'ventas_exportar',

            // ⚖️ DICTÁMENES - SUPERVISIÓN
            'dictamenes_ver',
            'dictamenes_ver_todos',
            'dictamenes_exportar',
            'dictamenes_aprobar',

            // 🏦 COMPRAS - SUPERVISIÓN
            'compras_ver',
            'compras_ver_todas',
            'compras_ver_historial',

            // 💰 PAGOS - SUPERVISIÓN FINANCIERA
            'pagos_ver',
            'pagos_ver_todos',
            'pagos_ver_dashboard',
            'pagos_exportar',

            // 💸 DEVOLUCIONES - APROBACIÓN FINAL
            'devoluciones_ver',
            'devoluciones_ver_todas',
            'devoluciones_aprobar_direccion',  // 🔑 AUTORIZACIÓN MÁXIMA

            // 🔄 VALIDACIONES - SUPERVISIÓN
            'validaciones_ver',
            'validaciones_ver_todas',
            'validaciones_ver_historial',

            // 🗂️ CATÁLOGOS - GESTIÓN COMPLETA
            'catalogos_sucursales',
            'catalogos_administradoras',
            'catalogos_tipos_juicio',
            'catalogos_etapas_procesales',
            'catalogos_estados_municipios',

            // 🎛️ CONFIGURACIÓN
            'configuracion_ver',
            'configuracion_editar',
            'configuracion_sistema',

            // 🔐 DESCUENTOS - AUTORIZACIÓN MÁXIMA
            'autorizar_descuentos_bajos',
            'autorizar_descuentos_medios',
            'autorizar_descuentos_altos',
        ]);

        // DIRECCIÓN COMERCIAL (DGC)
        $role = Role::firstOrCreate(['name' => 'Direccion_Comercial']);
        $role->syncPermissions([
            // 🏠 BASE
            'ver_panel_principal',
            'ver_actividad_sistema',

            // 🧭 NAVEGACIÓN - ÁREA COMERCIAL COMPLETA
            'menu_comercial',
            'menu_prospectos',
            'menu_clientes',
            'menu_propiedades',
            'menu_ventas',
            'menu_agenda',
            'menu_reportes',
            'menu_usuarios',     // Para gestionar su equipo

            // 📊 DASHBOARDS
            'dashboard_comercial',
            'dashboard_general',

            // 📈 REPORTES COMERCIALES
            'reportes_ventas',
            'reportes_cartera',
            'reportes_personalizados',

            // 👥 USUARIOS - GESTIÓN DE SU EQUIPO
            'usuarios_ver',
            'usuarios_crear',
            'usuarios_editar',
            'usuarios_asignar_rol',
            'usuarios_cambiar_sucursal',

            // 🎯 PROSPECTOS - GESTIÓN COMPLETA DE RED
            'prospectos_ver',
            'prospectos_crear',
            'prospectos_editar',
            'prospectos_eliminar',
            'prospectos_exportar',
            'prospectos_asignar',
            'prospectos_reasignar',          // 🔑 REASIGNAR ENTRE ASESORES
            'prospectos_importar',
            'prospectos_ver_todos',           // 🔑 VE TODA LA RED

            // 👤 CLIENTES - GESTIÓN COMPLETA
            'clientes_ver',
            'clientes_crear',
            'clientes_editar',
            'clientes_exportar',
            'clientes_ver_todos',             // 🔑 VE TODA LA RED
            'clientes_ver_expediente',
            'clientes_historial_completo',

            // 🏘️ PROPIEDADES / CARTERAS
            'propiedades_ver',
            'propiedades_crear',
            'propiedades_editar',
            'propiedades_exportar',
            'propiedades_asignar_cartera',    // 🔑 ASIGNA INVENTARIO A SUCURSALES (junto con DGE/GRS)
            'propiedades_cambiar_estatus',
            'propiedades_ver_historial',

            // 💼 VENTAS - GESTIÓN COMPLETA
            'ventas_ver',
            'ventas_crear',
            'ventas_editar',
            'ventas_cancelar',
            'ventas_ver_todas',               // 🔑 VE TODA LA RED
            'ventas_ver_historial',
            'ventas_agregar_nota',
            'ventas_registrar_apartado',
            'ventas_solicitar_contrato',
            'ventas_subir_contrato',
            'ventas_registrar_pago',

            // ⚖️ DICTÁMENES - SOLICITAR
            'dictamenes_ver',
            'dictamenes_solicitar',           // 🔑 PUEDE SOLICITAR DICTÁMENES
            'dictamenes_ver_todos',
            'dictamenes_exportar',

            // 📅 AGENDA
            'agenda_ver',
            'agenda_crear',
            'agenda_editar',
            'agenda_eliminar',
            'agenda_ver_todos',               // 🔑 VE AGENDA DE TODA LA RED
            'agenda_asignar_participantes',

            // 💬 INTERACCIONES
            'interacciones_ver',
            'interacciones_crear',
            'interacciones_editar',
            'interacciones_ver_todas',
            'interacciones_exportar',

            // 📎 ARCHIVOS
            'archivos_ver',
            'archivos_subir',
            'archivos_descargar',

            // 🔐 DESCUENTOS - AUTORIZACIÓN ALTA
            'autorizar_descuentos_bajos',
            'autorizar_descuentos_medios',
            'autorizar_descuentos_altos',     // 🔑 NIVEL MÁS ALTO
        ]);

        // DIRECCIÓN LEGAL (DIL) - arriba de UCP y URRJ
        $role = Role::firstOrCreate(['name' => 'Direccion_Legal']);
        $role->syncPermissions([
            'ver_panel_principal',
            'dashboard_juridico',
            'dictamenes_aprobar',
        ]);

        // --- NIVEL 2: GERENCIAS NACIONALES ---

        // GERENCIA REMATES Y SUCURSALES (GRS)
        $role = Role::firstOrCreate(['name' => 'GRS_Nacional']);
        $role->syncPermissions([
            // 🏠 BASE
            'ver_panel_principal',
            'ver_actividad_sistema',

            // 🧭 NAVEGACIÓN
            'menu_comercial',
            'menu_prospectos',
            'menu_clientes',
            'menu_propiedades',
            'menu_ventas',
            'menu_agenda',
            'menu_reportes',

            // 📊 DASHBOARDS
            'dashboard_comercial',

            // 📈 REPORTES
            'reportes_ventas',
            'reportes_cartera',

            // 🎯 PROSPECTOS - GESTIÓN TODA LA RED
            'prospectos_ver',
            'prospectos_crear',
            'prospectos_editar',
            'prospectos_eliminar',
            'prospectos_exportar',
            'prospectos_asignar',
            'prospectos_reasignar',
            'prospectos_ver_todos',           // 🔑 VE TODA LA RED

            // 👤 CLIENTES - GESTIÓN TODA LA RED
            'clientes_ver',
            'clientes_crear',
            'clientes_editar',
            'clientes_exportar',
            'clientes_ver_todos',
            'clientes_ver_expediente',
            'clientes_historial_completo',

            // 🏘️ PROPIEDADES / CARTERAS - ROL CLAVE
            'propiedades_ver',
            'propiedades_crear',
            'propiedades_editar',
            'propiedades_exportar',
            'propiedades_asignar_cartera',    // 🔑 FILTRA Y ASIGNA CARTERA A SUCURSALES (procedimiento PVEN-01)
            'propiedades_cambiar_estatus',
            'propiedades_subir_fotos',
            'propiedades_ver_historial',

            // 💼 VENTAS - GESTIÓN COMPLETA
            'ventas_ver',
            'ventas_crear',
            'ventas_editar',
            'ventas_cancelar',
            'ventas_ver_todas',
            'ventas_ver_historial',
            'ventas_agregar_nota',
            'ventas_registrar_apartado',
            'ventas_solicitar_contrato',
            'ventas_subir_contrato',
            'ventas_registrar_pago',

            // ⚖️ DICTÁMENES
            'dictamenes_ver',
            'dictamenes_solicitar',
            'dictamenes_ver_todos',

            // 📅 AGENDA
            'agenda_ver',
            'agenda_crear',
            'agenda_editar',
            'agenda_eliminar',
            'agenda_ver_todos',
            'agenda_asignar_participantes',

            // 💬 INTERACCIONES
            'interacciones_ver',
            'interacciones_crear',
            'interacciones_editar',
            'interacciones_ver_todas',

            // 📎 ARCHIVOS
            'archivos_ver',
            'archivos_subir',
            'archivos_descargar',

            // 🔐 DESCUENTOS
            'autorizar_descuentos_bajos',
            'autorizar_descuentos_medios',
            'autorizar_descuentos_altos',
        ]);

        // RESPONSABLE ATENCIÓN CLIENTE (RAC)
        $role = Role::firstOrCreate(['name' => 'RAC_Atencion_Cliente']);
        $role->syncPermissions([
            'ver_panel_principal',
        ]);

        // --- NIVEL 3: OPERACIÓN JURÍDICA ---

        // UCP (Consolidación - camino feliz: R2, R3, SVT)
        $role = Role::firstOrCreate(['name' => 'UCP_Consolidacion']);
        $role->syncPermissions([
            'ver_panel_principal',
            'dashboard_juridico',
            'dictamenes_elaborar',
            'propiedades_ver'
        ]);

        // 🆕 UFC (Formalización y Contratos - Notarías)
        Role::firstOrCreate(['name' => 'UFC_Formalizacion']);
        // Sin permisos asignados por ahora

        // URRJ (Resolución - casos negativos: R1, RV, RV1, RD)
        $role = Role::firstOrCreate(['name' => 'URRJ_Resolucion']);
        $role->syncPermissions([
            'ver_panel_principal',
            'dashboard_juridico',
        ]);

        // 🆕 UCM (Contenciosos Mercantiles / Litigantes)
        Role::firstOrCreate(['name' => 'UCM_Litigante']);
        // Sin permisos asignados por ahora

        // 🆕 UDP (Defensa Penal)
        Role::firstOrCreate(['name' => 'UDP_Defensa_Penal']);
        // Sin permisos asignados por ahora

        // --- NIVEL 4: ADMINISTRATIVO Y FINANZAS ---

        // GAD ADMINISTRACIÓN (Coordinador General)
        $role = Role::firstOrCreate(['name' => 'GAD_Administracion']);
        $role->syncPermissions([
            // 🏠 BASE
            'ver_panel_principal',
            'ver_actividad_sistema',

            // 🧭 NAVEGACIÓN
            'menu_administrativo',
            'menu_propiedades',              // 🔑 ACCESO A CARTERAS/PROPIEDADES
            'menu_catalogos',
            'menu_usuarios',

            // 🏘️ PROPIEDADES / CARTERAS - GESTIÓN TÉCNICA
            'propiedades_ver',
            'propiedades_crear',              // 🔑 CREA MAPAS EN MY MAPS (procedimiento PVEN-02)
            'propiedades_editar',
            'propiedades_ver_historial',

            // 👥 USUARIOS - GESTIÓN DE PERMISOS
            'usuarios_ver',
            'usuarios_editar',
            'usuarios_asignar_rol',           // 🔑 OTORGA PERMISOS EN MY MAPS

            // 👤 CLIENTES - VALIDACIÓN DE EXPEDIENTES
            'clientes_ver',
            'clientes_ver_todos',
            'clientes_ver_expediente',
            'clientes_validar_expediente',    // 🔑 VALIDA DOCUMENTACIÓN COMPLETA

            // 🗂️ CATÁLOGOS - GESTIÓN
            'catalogos_sucursales',
            'catalogos_administradoras',
            'catalogos_estados_municipios',

            // 💸 DEVOLUCIONES - VALIDACIÓN ADMINISTRATIVA
            'devoluciones_ver',
            'devoluciones_ver_todas',
            'devoluciones_validar_admin',     // 🔑 PRIMERA VALIDACIÓN

            // ✅ VALIDACIONES
            'validaciones_ver',
            'validaciones_aprobar',
            'validaciones_rechazar',
            'validaciones_ver_todas',
        ]);

        // GAD FINANZAS (Legacy - mantener por compatibilidad)
        $role = Role::firstOrCreate(['name' => 'GAD_Finanzas']);
        $role->syncPermissions([
            'ver_panel_principal',
            'dashboard_financiero',
        ]);

        // 🆕 PCA (Tesorería - Pagos, Cobros y Archivos)
        Role::firstOrCreate(['name' => 'PCA_Tesoreria']);
        // Sin permisos asignados por ahora

        // 🆕 UFF (Fiscalización y Facturación)
        Role::firstOrCreate(['name' => 'UFF_Fiscalizacion']);
        // Sin permisos asignados por ahora

        // --- NIVEL 5: OPERACIÓN COMERCIAL ---

        // GERENTE REGIONAL (SVT)
        $role = Role::firstOrCreate(['name' => 'SVT_Gerente_Regional']);
        $role->syncPermissions([
            // 🏠 BASE
            'ver_panel_principal',
            'ver_actividad_sistema',

            // 🧭 NAVEGACIÓN
            'menu_comercial',
            'menu_prospectos',
            'menu_clientes',
            'menu_propiedades',
            'menu_ventas',
            'menu_agenda',
            'menu_interacciones',

            // 📊 DASHBOARDS
            'dashboard_comercial',

            // 📈 REPORTES - SU SUCURSAL
            'reportes_ventas',
            'reportes_cartera',

            // 🎯 PROSPECTOS - GESTIÓN DE SU SUCURSAL
            'prospectos_ver',
            'prospectos_crear',
            'prospectos_editar',
            'prospectos_eliminar',
            'prospectos_exportar',
            'prospectos_asignar',
            'prospectos_reasignar',
            'prospectos_ver_sucursal_completa',

            // 👤 CLIENTES - GESTIÓN DE SU SUCURSAL
            'clientes_ver',
            'clientes_crear',
            'clientes_editar',
            'clientes_exportar',
            'clientes_ver_expediente',
            'clientes_historial_completo',
            // NOTA: NO tiene clientes_ver_todos (solo su sucursal)

            // 🏘️ PROPIEDADES - GESTIÓN MY MAPS
            'propiedades_ver',
            'propiedades_crear',
            'propiedades_editar',              // 🔑 ASIGNA UBICACIONES EN MY MAPS (procedimiento PVEN-02)
            'propiedades_cambiar_estatus',
            'propiedades_subir_fotos',         // 🔑 SUBE FOTOS DE INMUEBLES
            'propiedades_ver_historial',
            // NOTA: NO tiene propiedades_asignar_cartera (eso es de GRS/DGC)

            // 💼 VENTAS - GESTIÓN COMPLETA DE SU SUCURSAL
            'ventas_ver',
            'ventas_crear',
            'ventas_editar',
            'ventas_cancelar',
            'ventas_ver_historial',
            'ventas_agregar_nota',
            'ventas_registrar_apartado',
            'ventas_solicitar_contrato',
            'ventas_subir_contrato',
            'ventas_registrar_pago',
            // NOTA: NO tiene ventas_ver_todas (solo su sucursal)

            // ⚖️ DICTÁMENES
            'dictamenes_ver',
            'dictamenes_solicitar',

            // 📅 AGENDA - DE SU EQUIPO
            'agenda_ver',
            'agenda_crear',
            'agenda_editar',
            'agenda_eliminar',
            'agenda_asignar_participantes',
            // NOTA: NO tiene agenda_ver_todos (solo su sucursal)

            // 💬 INTERACCIONES - DE SU EQUIPO
            'interacciones_ver',
            'interacciones_crear',
            'interacciones_editar',
            // NOTA: NO tiene interacciones_ver_todas (solo su sucursal)

            // 📎 ARCHIVOS
            'archivos_ver',
            'archivos_subir',
            'archivos_descargar',

            // 🔐 DESCUENTOS
            'autorizar_descuentos_bajos',     // 🔑 NIVEL GERENTE
            'autorizar_descuentos_medios',
        ]);

        // ASESOR (SVT)
        $role = Role::firstOrCreate(['name' => 'SVT_Asesor']);
        $role->syncPermissions([
            // 🏠 BASE
            'ver_panel_principal',

            // 🧭 NAVEGACIÓN
            'menu_comercial',
            'menu_prospectos',
            'menu_clientes',
            'menu_propiedades',
            'menu_ventas',
            'menu_agenda',
            'menu_interacciones',

            // 📊 DASHBOARDS
            'dashboard_comercial',

            // 🎯 PROSPECTOS - SOLO LOS SUYOS
            'prospectos_ver',                 // 🔑 VE SOLO LOS ASIGNADOS A ÉL (scope en Resource)
            'prospectos_crear',
            'prospectos_editar',              // 🔑 SOLO LOS SUYOS
            'prospectos_exportar',
            // NOTA: NO puede asignar, reasignar ni ver todos

            // 👤 CLIENTES - SOLO LOS SUYOS
            'clientes_ver',                   // 🔑 VE SOLO LOS SUYOS (scope)
            'clientes_crear',
            'clientes_editar',                // 🔑 SOLO LOS SUYOS
            'clientes_ver_expediente',
            'clientes_editar_expediente',

            // 🏘️ PROPIEDADES - SOLO CONSULTA INVENTARIO
            'propiedades_ver',                // 🔑 VE INVENTARIO DISPONIBLE DE SU SUCURSAL
            // NOTA: NO puede crear, editar, ni asignar carteras

            // 💼 VENTAS - SOLO LAS SUYAS
            'ventas_ver',                     // 🔑 VE SOLO SUS VENTAS
            'ventas_crear',
            'ventas_editar',                  // 🔑 SOLO LAS SUYAS
            'ventas_ver_historial',
            'ventas_agregar_nota',
            'ventas_registrar_apartado',
            'ventas_solicitar_contrato',
            'ventas_subir_contrato',
            'ventas_registrar_pago',

            // ⚖️ DICTÁMENES
            'dictamenes_ver',                 // 🔑 VE SOLO LOS RELACIONADOS A SUS CLIENTES
            'dictamenes_solicitar',

            // 📅 AGENDA - SOLO LA SUYA
            'agenda_ver',                     // 🔑 VE SOLO SU AGENDA
            'agenda_crear',
            'agenda_editar',
            'agenda_eliminar',

            // 💬 INTERACCIONES - SOLO LAS SUYAS
            'interacciones_ver',              // 🔑 VE SOLO SUS INTERACCIONES
            'interacciones_crear',
            'interacciones_editar',

            // 📎 ARCHIVOS
            'archivos_ver',
            'archivos_subir',
            'archivos_descargar',
        ]);

        // --- NIVEL 6: ATENCIÓN AL CLIENTE ---

        // 🆕 ATC (Atención Telefónica)
        Role::firstOrCreate(['name' => 'ATC_Telefonista']);
        // Sin permisos asignados por ahora

        // 🆕 UAC (Staff Atención al Cliente)
        Role::firstOrCreate(['name' => 'UAC_Staff']);
        // Sin permisos asignados por ahora

        // --- SUPER ADMIN ---

        // SUPER ADMIN
        $role = Role::firstOrCreate(['name' => 'Super_Admin']);
        $role->givePermissionTo(Permission::all());

        $this->command->info('✅ Roles y Permisos creados exitosamente!');
        $this->command->info('📊 Total Roles: ' . Role::count());
        $this->command->info('🔑 Total Permisos: ' . Permission::count());
    }
}
