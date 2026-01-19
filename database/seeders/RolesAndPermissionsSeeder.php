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
            // --- BASE ---
            'ver_panel_principal',
            'ver_actividad_sistema',

            // --- COMERCIAL (SVT / GRS / DGC) ---
            'ver_tablero_comercial',
            'gestionar_prospectos_propios', // Asesor
            'gestionar_sucursal_propia',    // Gerente Regional
            'gestionar_toda_la_red',        // GRS y DGC (Ver todas las sucursales)
            'solicitar_apartado',
            'solicitar_dictamen',
            'ver_inventario_disponible',
            'autorizar_descuentos_bajos',   // Gerente Regional
            'autorizar_descuentos_altos',   // GRS y DGC

            // --- ADMINISTRATIVO Y FINANCIERO (GAD) ---
            'validar_expedientes_admin',    // GAD Admin
            'administrar_catalogos',
            'asignar_gestores',
            'ver_tablero_finanzas',
            'validar_ingresos_bancos',      // GAD Finanzas (PCA/UFF unificados)
            'autorizar_egresos',
            'gestionar_facturacion',

            // --- JURÍDICO: CONSOLIDACIÓN (UCP - R2, R3) ---
            'ver_mesa_control_juridica',
            'dictaminar_viabilidad',        // El "Sí/No" legal
            'gestionar_proceso_compra',     // Negociación con Banco
            'gestionar_formalizacion',      // Contratos y Escrituras

            // --- JURÍDICO: RESOLUCIÓN (URRJ - R1, RV) ---
            'gestionar_casos_conflictivos', // R1, RV
            'autorizar_devoluciones_legal', // Visto bueno legal para devolver dinero
            'gestionar_cambios_garantia',   // RV (Cambios de casa)

            // --- JURÍDICO: LITIGIO (UCM) ---
            'gestionar_litigio',            // Juzgados

            // --- ATENCIÓN AL CLIENTE (UAC / RAC) ---
            'ver_estatus_cliente',          // Solo lectura para informar
            'solicitar_devolucion',         // Iniciar el ticket (Staff UAC)
            'autorizar_devolucion_servicio', // Visto bueno de servicio (RAC)

            // --- SISTEMA ---
            'administrar_usuarios',
            'administrar_roles',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // ==========================================
        // 3. CREACIÓN DE ROLES
        // ==========================================

        // --- NIVEL 1: DIRECTORES ESTRATÉGICOS ---

        // DIRECCIÓN COMERCIAL (DGC)
        $role = Role::firstOrCreate(['name' => 'Direccion_Comercial']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_tablero_comercial',
            'gestionar_toda_la_red',
            'ver_inventario_disponible',
            'autorizar_descuentos_altos'
        ]);

        // DIRECCIÓN LEGAL (DIL) - arriba de UCP y URRJ
        $role = Role::firstOrCreate(['name' => 'Direccion_Legal']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_mesa_control_juridica',
            'dictaminar_viabilidad',
            'gestionar_proceso_compra',
            'gestionar_formalizacion',
            'gestionar_casos_conflictivos',
            'autorizar_devoluciones_legal',
            'gestionar_litigio'
        ]);

        // --- NIVEL 2: GERENCIAS NACIONALES ---

        // GERENCIA REMATES Y SUCURSALES (GRS)
        $role = Role::firstOrCreate(['name' => 'GRS_Nacional']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_tablero_comercial',
            'gestionar_toda_la_red',      // Ve lo mismo que el Director, pero opera
            'ver_inventario_disponible',
            'autorizar_descuentos_altos', // Tiene buen nivel de firma
            'solicitar_apartado'          // Puede vender si es necesario
        ]);

        // RESPONSABLE ATENCIÓN CLIENTE (RAC)
        $role = Role::firstOrCreate(['name' => 'RAC_Atencion_Cliente']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_estatus_cliente',
            'solicitar_devolucion',
            'autorizar_devolucion_servicio' // Su firma es requerida
        ]);

        // --- NIVEL 3: OPERACIÓN JURÍDICA Y ADMIN ---

        // UCP (Consolidación - camino feliz)
        $role = Role::firstOrCreate(['name' => 'UCP_Consolidacion']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_mesa_control_juridica',
            'dictaminar_viabilidad',
            'gestionar_proceso_compra',
            'gestionar_formalizacion',
            'ver_inventario_disponible'
        ]);

        // URRJ (Resolución - casos conflictivos)
        $role = Role::firstOrCreate(['name' => 'URRJ_Resolucion']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_mesa_control_juridica',
            'gestionar_casos_conflictivos',
            'autorizar_devoluciones_legal',
            'gestionar_cambios_garantia'
        ]);

        // GAD FINANZAS (PCA + UFF)
        $role = Role::firstOrCreate(['name' => 'GAD_Finanzas']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_tablero_finanzas',
            'validar_ingresos_bancos',
            'autorizar_egresos',
            'gestionar_facturacion'
        ]);

        // GAD ADMINISTRACIÓN (Validación)
        $role = Role::firstOrCreate(['name' => 'GAD_Administracion']);
        $role->syncPermissions([
            'ver_panel_principal',
            'validar_expedientes_admin',
            'administrar_catalogos',
            'asignar_gestores'
        ]);

        // --- NIVEL 4: OPERACIÓN REGIONAL Y STAFF ---

        // GERENTE REGIONAL (SVT)
        $role = Role::firstOrCreate(['name' => 'SVT_Gerente_Regional']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_tablero_comercial',
            'gestionar_sucursal_propia', // Scope: Su ciudad
            'solicitar_apartado',
            'solicitar_dictamen',
            'ver_inventario_disponible',
            'autorizar_descuentos_bajos'
        ]);

        // UAC (Staff Atención Cliente)
        $role = Role::firstOrCreate(['name' => 'UAC_Staff']);
        $role->syncPermissions([
            'ver_panel_principal',
            'ver_estatus_cliente',
            'solicitar_devolucion' // Solo inicia el trámite
        ]);

        // ABOGADO LITIGANTE (UCM)
        $role = Role::firstOrCreate(['name' => 'Abogado_Litigante']);
        $role->syncPermissions([
            'ver_panel_principal',
            'gestionar_litigio'
        ]);

        // --- NIVEL 5: FUERZA DE VENTAS ---

        // ASESOR (SVT)
        $role = Role::firstOrCreate(['name' => 'SVT_Asesor']);
        $role->syncPermissions([
            'ver_panel_principal',
            'gestionar_prospectos_propios',
            'solicitar_apartado',
            'solicitar_dictamen',
            'ver_inventario_disponible'
        ]);

        // SUPER ADMIN
        $role = Role::firstOrCreate(['name' => 'Super_Admin']);
        $role->givePermissionTo(Permission::all());
    }
}
