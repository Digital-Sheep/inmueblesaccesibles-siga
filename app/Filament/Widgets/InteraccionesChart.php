<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Interaccion;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class InteraccionesChart extends ChartWidget
{
    protected ?string $heading = 'Actividad Comercial (Últimos 30 días)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // Usamos un paquete helper de Filament para agrupar datos por fecha
        $data = Trend::model(Interaccion::class)
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Interacciones',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#3b82f6', // Azul
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // O 'bar', 'pie', 'doughnut'
    }
}
