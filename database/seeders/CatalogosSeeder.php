<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatSucursal;
use App\Models\CatAdministradora;
use App\Models\CatTipoJuicio;
use App\Models\CatEtapaProcesal;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SUCURSALES
        $sucursales = [
            ['nombre' => 'Culiacán', 'abreviatura' => 'CUL'],
            ['nombre' => 'Mazatlán', 'abreviatura' => 'MZT'],
            ['nombre' => 'La Paz', 'abreviatura' => 'LPZ'],
            ['nombre' => 'Guadalajara', 'abreviatura' => 'GDL'],
        ];

        foreach ($sucursales as $sucursal) {
            CatSucursal::firstOrCreate(
                ['nombre' => $sucursal['nombre']],
                ['abreviatura' => $sucursal['abreviatura'], 'activo' => true]
            );
        }

        // 2. ADMINISTRADORAS (Bancos)
        $administradoras = [
            'ZENDERE',
            'PENDULUM',
            'BBVA',
            'ADANAMANTINE',
            'SANTANDER',
            'GLOSAN',
            'ADN',
            'OPERAX',
            'SHF',
            'GARANTIAS PROPIAS',
            'TERTIUS'
        ];

        foreach ($administradoras as $nombre) {
            CatAdministradora::firstOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }

        // 3. TIPOS DE JUICIO
        $tiposJuicio = [
            'Especial Hipotecario',
            'Ordinario Civil',
            'Ejecutivo Mercantil',
            'Ordinario Mercantil',
            'Jurisdicción Voluntaria',
            'Oral Mercantil',
            'Sumario Civil',
        ];

        foreach ($tiposJuicio as $nombre) {
            CatTipoJuicio::firstOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }

        // 4. ETAPAS PROCESALES (Timeline Legal)
        // Las dejamos genéricas (null en tipo_juicio_id) para que apliquen a todos por defecto
        $etapas = [
            10 => 'Presentación de Demanda / Apersonamiento',
            20 => 'Emplazamiento',
            30 => 'Contestación de Demanda',
            40 => 'Ofrecimiento de Pruebas',
            50 => 'Desahogo de Pruebas',
            60 => 'Alegatos',
            70 => 'Sentencia Definitiva',
            80 => 'Apelación / Amparo',
            90 => 'Ejecución de Sentencia',
            100 => 'Avalúos',
            110 => 'Remate (Almonedas)',
            120 => 'Adjudicación',
            130 => 'Escrituración',
            140 => 'Toma de Posesión / Desalojo',
            150 => 'Entrega al Cliente',
        ];

        foreach ($etapas as $orden => $nombre) {
            CatEtapaProcesal::firstOrCreate(
                ['nombre' => $nombre],
                [
                    'orden' => $orden,
                    'dias_termino_legal' => 15, // Valor por defecto para el semáforo
                    'tipo_juicio_id' => null // Aplica a todos
                ]
            );
        }
    }
}
