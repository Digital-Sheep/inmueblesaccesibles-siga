<?php

namespace App\Filament\Widgets;


use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Prospecto;
use App\Models\Interaccion;
use App\Models\EventoAgenda;

class ResumenComercialStats extends BaseWidget
{
    // protected static ?string $pollingInterval = '15s';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        return [
            // TARJETA 1: Clientes/Prospectos
            Stat::make('Total Prospectos', Prospecto::count())
                ->description('Base de datos activa')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]), // Gráfica pequeña de adorno

            // TARJETA 2: Ventas/Citas del Mes
            Stat::make('Citas este Mes', EventoAgenda::whereMonth('fecha_inicio', now()->month)->count())
                ->description('Agenda comercial')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            // TARJETA 3: Tareas Pendientes
            Stat::make('Pendientes', Interaccion::where('fecha_programada', '<=', now())->count())
                ->description('Atención requerida')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('danger'),
        ];
    }
}
