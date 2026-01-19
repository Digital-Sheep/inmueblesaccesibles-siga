<?php

namespace Database\Seeders;

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

        // 3. Usuarios de Prueba
        $this->call(UsuariosPruebaSeeder::class);

        // 4. Prospectos de Prueba
        $this->call(ProspectosSeeder::class);
    }
}
