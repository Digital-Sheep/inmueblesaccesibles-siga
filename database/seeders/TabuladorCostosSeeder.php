<?php

namespace Database\Seeders;

use App\Models\CatTabuladorCosto;
use Illuminate\Database\Seeder;

class TabuladorCostosSeeder extends Seeder
{
    public function run(): void
    {
        $costosPorTamano = [
            'CHICA' => [
                'costo_remodelacion' => 80000.00,
                'costo_luz' => 3000.00,
                'costo_agua' => 2000.00,
                'costo_predial' => 5000.00,
                'costo_gastos_juridicos' => 20000.00, // Juicio + Notariales
            ],
            'MEDIANA' => [
                'costo_remodelacion' => 150000.00,
                'costo_luz' => 5000.00,
                'costo_agua' => 3000.00,
                'costo_predial' => 8000.00,
                'costo_gastos_juridicos' => 30000.00,
            ],
            'GRANDE' => [
                'costo_remodelacion' => 250000.00,
                'costo_luz' => 8000.00,
                'costo_agua' => 5000.00,
                'costo_predial' => 12000.00,
                'costo_gastos_juridicos' => 40000.00,
            ],
            'MUY_GRANDE' => [
                'costo_remodelacion' => 400000.00,
                'costo_luz' => 12000.00,
                'costo_agua' => 8000.00,
                'costo_predial' => 18000.00,
                'costo_gastos_juridicos' => 50000.00,
            ],
        ];

        foreach ($costosPorTamano as $tamano => $costos) {
            CatTabuladorCosto::updateOrCreate(
                ['tamano_propiedad' => $tamano],
                array_merge($costos, [
                    'activo' => true,
                    'updated_by' => 1, // Usuario sistema
                ])
            );
        }

        $this->command->info('✅ Tabulador de costos cargado: 4 configuraciones');
        $this->command->table(
            ['Tamaño', 'Remodelación', 'Luz', 'Agua', 'Predial', 'Gastos Jurídicos', 'TOTAL'],
            collect($costosPorTamano)->map(function ($costos, $tamano) {
                $total = array_sum($costos);
                return [
                    $tamano,
                    '$' . number_format($costos['costo_remodelacion'], 0),
                    '$' . number_format($costos['costo_luz'], 0),
                    '$' . number_format($costos['costo_agua'], 0),
                    '$' . number_format($costos['costo_predial'], 0),
                    '$' . number_format($costos['costo_gastos_juridicos'], 0),
                    '$' . number_format($total, 0),
                ];
            })->values()->toArray()
        );
    }
}
