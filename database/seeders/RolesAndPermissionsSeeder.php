<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. CREAR PERMISOS (Granulares)
        $permisos = [
            // Módulo Comercial
            'ver_tablero_comercial',
            'gestionar_prospectos_propios',
            'gestionar_todos_prospectos', // Para Gerentes
            'solicitar_apartado',
            'solicitar_dictamen',
            'ver_inventario_disponible',

            // Módulo Jurídico
            'ver_mesa_control_juridica',
            'dictaminar_r1_r2',
            'gestionar_expedientes_litigio', // Abogados UCM
            'gestionar_formalizacion', // UCP (Contratos)
            'gestionar_cambios_rv', // URRJ

            // Módulo Financiero
            'ver_tablero_finanzas',
            'validar_pagos_apartado',
            'validar_pagos_enganche',

            // Admin
            'administrar_usuarios',
            'administrar_catalogos',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // 2. CREAR ROLES Y ASIGNAR PERMISOS (Basado en Manuales)

        // ASESOR (Ventas pura)
        $role = Role::firstOrCreate(['name' => 'Asesor']);
        $role->syncPermissions([
            'ver_tablero_comercial',
            'gestionar_prospectos_propios',
            'solicitar_apartado',
            'solicitar_dictamen',
            'ver_inventario_disponible'
        ]);

        // GERENTE DE SUCURSAL (Supervisa Ventas)
        $role = Role::firstOrCreate(['name' => 'Gerente_Sucursal']);
        $role->syncPermissions([
            'ver_tablero_comercial',
            'gestionar_todos_prospectos',
            'solicitar_apartado',
            'solicitar_dictamen',
            'ver_inventario_disponible'
        ]);

        // UCP (Consolidación Patrimonial - El "Dueño" del proceso exitoso)
        $role = Role::firstOrCreate(['name' => 'UCP']);
        $role->syncPermissions([
            'ver_mesa_control_juridica',
            'dictaminar_r1_r2',
            'gestionar_formalizacion',
            'ver_inventario_disponible' // Para ver qué se vende
        ]);

        // URRJ (Resolución - Los "Bomberos" de casos difíciles)
        $role = Role::firstOrCreate(['name' => 'URRJ']);
        $role->syncPermissions([
            'ver_mesa_control_juridica',
            'dictaminar_r1_r2',
            'gestionar_cambios_rv',
            'gestionar_prospectos_propios' // Para gestionar la recuperación
        ]);

        // UCM (Litigantes - Los que van al juzgado)
        $role = Role::firstOrCreate(['name' => 'Abogado_Litigante']);
        $role->syncPermissions([
            'gestionar_expedientes_litigio',
            'ver_mesa_control_juridica'
        ]);

        // GAD (Administración y Finanzas)
        $role = Role::firstOrCreate(['name' => 'Administracion']);
        $role->syncPermissions([
            'ver_tablero_finanzas',
            'validar_pagos_apartado',
            'validar_pagos_enganche',
            'administrar_catalogos'
        ]);

        // DIL (Dirección Legal) y DGE (Dirección General) -> SUPER ADMIN
        $role = Role::firstOrCreate(['name' => 'Super_Admin']);
        // El Super Admin en Filament Shield suele tener acceso total por defecto,
        // pero se lo asignamos explícitamente a todo por si acaso.
        $role->givePermissionTo(Permission::all());
    }
}
