<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Catálogos Base
        $this->call(MexicoGeoSeeder::class);
        $this->call(CatalogosSeeder::class);

        // 2. Seguridad
        $this->call(RolesAndPermissionsSeeder::class);

        // 3. Usuario Super Admin
        // firstOrCreate para no duplicar
        $admin = User::firstOrCreate(
            ['email' => 'luis@digitalsheep.mx'],
            [
                'name' => 'Luis Peña',
                'password' => bcrypt('pass'),
                'telefono' => '3300000000',
                'activo' => true,
                'sucursal_id' => \App\Models\CatSucursal::where('abreviatura', 'GDL')->first()?->id,
            ]
        );

        // Asignar Rol
        if (!$admin->hasRole('Super_Admin')) {
            $admin->assignRole('Super_Admin');
        }

        // 4. Usuario de Prueba
        $asesor = User::firstOrCreate(
            ['email' => 'asesor@inmueblesaccesibles.com'],
            [
                'name' => 'Asesor Demo',
                'password' => bcrypt('pass'),
                'telefono' => '3311111111',
                'activo' => true,
                'sucursal_id' => \App\Models\CatSucursal::where('abreviatura', 'GDL')->first()?->id,
            ]
        );

        if (!$asesor->hasRole('Asesor')) {
            $asesor->assignRole('Asesor');
        }
    }
}
