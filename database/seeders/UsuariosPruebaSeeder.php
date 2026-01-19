<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CatSucursal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuariosPruebaSeeder extends Seeder
{
    protected $password;

    public function __construct()
    {
        $this->password = Hash::make('pass');
    }

    public function run(): void
    {
        $this->command->info('🌱 Generando Usuarios con Roles Reales...');

        // ----------------------------------------------------------------
        // 1. CORPORATIVO Y DIRECTIVOS (MATRIZ GDL)
        // ----------------------------------------------------------------

        // Super Admin
        $this->crearUsuario('Luis Peña', 'luis@digitalsheep.mx', 'Super_Admin', 'GDL');

        // Dirección Comercial
        $this->crearUsuario('Director Comercial', 'dir.comercial@inmuebles.com', 'Direccion_Comercial', 'GDL');

        // Dirección Legal
        $this->crearUsuario('Director Legal', 'dir.legal@inmuebles.com', 'Direccion_Legal', 'GDL');

        // Gerencia Nacional (Supervisor de Gerentes Regionales)
        $this->crearUsuario('Gerente Nacional GRS', 'grs.nacional@inmuebles.com', 'GRS_Nacional', 'GDL');

        // Administración y Finanzas
        $this->crearUsuario('Gerente Admin', 'gad.admin@inmuebles.com', 'GAD_Administracion', 'GDL');
        $this->crearUsuario('Gerente Finanzas', 'gad.finanzas@inmuebles.com', 'GAD_Finanzas', 'GDL');


        // ----------------------------------------------------------------
        // 2. OPERACIÓN POR SUCURSALES (GERENTES Y ASESORES)
        // ----------------------------------------------------------------
        $sucursales = ['CUL', 'MZT', 'LPZ', 'GDL'];

        foreach ($sucursales as $abv) {
            // Validar existencia de sucursal
            if (!CatSucursal::where('abreviatura', $abv)->exists()) {
                continue;
            }

            // A. Gerente Regional de la Sucursal (SVT_Gerente_Regional)
            $this->crearUsuario(
                "Gerente $abv",
                "gerente." . strtolower($abv) . "@inmuebles.com",
                'SVT_Gerente_Regional',
                $abv
            );

            // B. 3 Asesores por Sucursal (SVT_Asesor)
            for ($i = 1; $i <= 3; $i++) {
                $this->crearUsuario(
                    "Asesor $abv $i",
                    "asesor$i." . strtolower($abv) . "@inmuebles.com",
                    'SVT_Asesor',
                    $abv
                );
            }
        }

        // ----------------------------------------------------------------
        // 3. JURÍDICO OPERATIVO (SOPORTE)
        // ----------------------------------------------------------------

        // UCP Consolidación (Mesa de Control / Validación inicial)
        $this->crearUsuario('Lic. Consolidación', 'ucp.mesa@inmuebles.com', 'UCP_Consolidacion', 'GDL');

        // URRJ Resolución (Conflictos / Devoluciones)
        $this->crearUsuario('Lic. Resolución', 'urrj.resolucion@inmuebles.com', 'URRJ_Resolucion', 'GDL');

        // Abogado Litigante (Juzgados)
        $this->crearUsuario('Abogado Litigante', 'abogado.litigio@inmuebles.com', 'Abogado_Litigante', 'GDL');


        // ----------------------------------------------------------------
        // 4. ATENCIÓN A CLIENTES (POST-VENTA)
        // ----------------------------------------------------------------

        // RAC (Responsable / Gerente de Atención)
        $this->crearUsuario('Gerente Atn Clientes', 'rac.atencion@inmuebles.com', 'RAC_Atencion_Cliente', 'GDL');

        // UAC (Staff operativo)
        $this->crearUsuario('Staff Atn Clientes', 'uac.staff@inmuebles.com', 'UAC_Staff', 'GDL');
    }

    /**
     * Método auxiliar privado
     */
    private function crearUsuario($nombre, $email, $rolNombre, $sucursalAbv)
    {
        // Buscar ID de sucursal o fallback a la primera
        $sucursal = CatSucursal::where('abreviatura', $sucursalAbv)->first();
        $sucursalId = $sucursal ? $sucursal->id : CatSucursal::first()->id;

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $nombre,
                'password' => $this->password,
                'telefono' => '33' . rand(10000000, 99999999),
                'activo' => true,
                'sucursal_id' => $sucursalId,
            ]
        );

        // Asignación de Rol Segura
        if (Role::where('name', $rolNombre)->exists()) {
            if (!$user->hasRole($rolNombre)) {
                $user->assignRole($rolNombre);
            }
        } else {
            // Alerta en consola si escribiste mal el rol en el array
            $this->command->warn("⚠️ El Rol '$rolNombre' NO existe en la base de datos. Usuario '$nombre' creado sin permisos.");
        }

        return $user;
    }
}
