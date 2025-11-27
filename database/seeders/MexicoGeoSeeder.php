<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MexicoGeoSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivar restricciones de llaves foráneas para limpiar tablas
        Schema::disableForeignKeyConstraints();
        DB::table('cat_municipios')->truncate();
        DB::table('cat_estados')->truncate();
        Schema::enableForeignKeyConstraints();

        $estados = [
            ['nombre' => 'Aguascalientes', 'abreviatura' => 'AGS', 'municipios' => ['Aguascalientes', 'Jesús María', 'Calvillo']],
            ['nombre' => 'Baja California', 'abreviatura' => 'BC', 'municipios' => ['Tijuana', 'Mexicali', 'Ensenada', 'Tecate', 'Rosarito']],
            ['nombre' => 'Baja California Sur', 'abreviatura' => 'BCS', 'municipios' => ['La Paz', 'Los Cabos', 'Loreto']],
            ['nombre' => 'Campeche', 'abreviatura' => 'CAMP', 'municipios' => ['Campeche', 'Carmen', 'Champotón']],
            ['nombre' => 'Coahuila', 'abreviatura' => 'COAH', 'municipios' => ['Saltillo', 'Torreón', 'Monclova', 'Piedras Negras']],
            ['nombre' => 'Colima', 'abreviatura' => 'COL', 'municipios' => ['Colima', 'Manzanillo', 'Tecomán']],
            ['nombre' => 'Chiapas', 'abreviatura' => 'CHIS', 'municipios' => ['Tuxtla Gutiérrez', 'San Cristóbal de las Casas', 'Tapachula']],
            ['nombre' => 'Chihuahua', 'abreviatura' => 'CHIH', 'municipios' => ['Chihuahua', 'Juárez', 'Cuauhtémoc', 'Delicias']],
            ['nombre' => 'Ciudad de México', 'abreviatura' => 'CDMX', 'municipios' => ['Coyoacán', 'Cuauhtémoc', 'Iztapalapa', 'Benito Juárez', 'Miguel Hidalgo']],
            ['nombre' => 'Durango', 'abreviatura' => 'DGO', 'municipios' => ['Durango', 'Gómez Palacio', 'Lerdo']],
            ['nombre' => 'Guanajuato', 'abreviatura' => 'GTO', 'municipios' => ['León', 'Guanajuato', 'Irapuato', 'Celaya']],
            ['nombre' => 'Guerrero', 'abreviatura' => 'GRO', 'municipios' => ['Acapulco', 'Chilpancingo', 'Zihuatanejo']],
            ['nombre' => 'Hidalgo', 'abreviatura' => 'HGO', 'municipios' => ['Pachuca', 'Tulancingo', 'Tula']],
            ['nombre' => 'Jalisco', 'abreviatura' => 'JAL', 'municipios' => ['Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tlajomulco', 'Tonalá', 'Puerto Vallarta']],
            ['nombre' => 'México', 'abreviatura' => 'MEX', 'municipios' => ['Toluca', 'Naucalpan', 'Ecatepec', 'Tlalnepantla', 'Metepec']],
            ['nombre' => 'Michoacán', 'abreviatura' => 'MICH', 'municipios' => ['Morelia', 'Uruapan', 'Zamora']],
            ['nombre' => 'Morelos', 'abreviatura' => 'MOR', 'municipios' => ['Cuernavaca', 'Jiutepec', 'Cuautla']],
            ['nombre' => 'Nayarit', 'abreviatura' => 'NAY', 'municipios' => ['Tepic', 'Bahía de Banderas']],
            ['nombre' => 'Nuevo León', 'abreviatura' => 'NL', 'municipios' => ['Monterrey', 'San Pedro Garza García', 'Apodaca', 'Guadalupe']],
            ['nombre' => 'Oaxaca', 'abreviatura' => 'OAX', 'municipios' => ['Oaxaca de Juárez', 'Salina Cruz', 'Puerto Escondido']],
            ['nombre' => 'Puebla', 'abreviatura' => 'PUE', 'municipios' => ['Puebla', 'Tehuacán', 'Cholula']],
            ['nombre' => 'Querétaro', 'abreviatura' => 'QRO', 'municipios' => ['Querétaro', 'Corregidora', 'El Marqués']],
            ['nombre' => 'Quintana Roo', 'abreviatura' => 'QROO', 'municipios' => ['Cancún', 'Playa del Carmen', 'Tulum', 'Chetumal']],
            ['nombre' => 'San Luis Potosí', 'abreviatura' => 'SLP', 'municipios' => ['San Luis Potosí', 'Soledad']],
            ['nombre' => 'Sinaloa', 'abreviatura' => 'SIN', 'municipios' => ['Culiacán', 'Mazatlán', 'Los Mochis', 'Guasave']],
            ['nombre' => 'Sonora', 'abreviatura' => 'SON', 'municipios' => ['Hermosillo', 'Ciudad Obregón', 'Nogales']],
            ['nombre' => 'Tabasco', 'abreviatura' => 'TAB', 'municipios' => ['Villahermosa', 'Cárdenas']],
            ['nombre' => 'Tamaulipas', 'abreviatura' => 'TAM', 'municipios' => ['Reynosa', 'Matamoros', 'Tampico', 'Ciudad Victoria']],
            ['nombre' => 'Tlaxcala', 'abreviatura' => 'TLAX', 'municipios' => ['Tlaxcala', 'Apizaco']],
            ['nombre' => 'Veracruz', 'abreviatura' => 'VER', 'municipios' => ['Veracruz', 'Xalapa', 'Coatzacoalcos', 'Boca del Río']],
            ['nombre' => 'Yucatán', 'abreviatura' => 'YUC', 'municipios' => ['Mérida', 'Valladolid', 'Progreso']],
            ['nombre' => 'Zacatecas', 'abreviatura' => 'ZAC', 'municipios' => ['Zacatecas', 'Guadalupe', 'Fresnillo']],
        ];

        foreach ($estados as $estado) {
            // 1. Insertar Estado
            $estadoId = DB::table('cat_estados')->insertGetId([
                'nombre' => $estado['nombre'],
                'abreviatura' => $estado['abreviatura'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Insertar Municipios
            foreach ($estado['municipios'] as $municipio) {
                DB::table('cat_municipios')->insert([
                    'estado_id' => $estadoId,
                    'nombre' => $municipio,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
