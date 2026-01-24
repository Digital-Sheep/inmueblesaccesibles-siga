<?php

namespace App\Filament\Widgets;

use App\Models\Dictamen;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DictamenesJuridicosWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['Direccion_Legal', 'DGE', 'Super_Admin']);
    }

    protected function getStats(): array
    {
        // Dictámenes pendientes de atender
        $pendientes = Dictamen::where('estatus', 'PENDIENTE')->count();

        // En proceso de investigación
        $enProceso = Dictamen::where('estatus', 'EN_INVESTIGACION')->count();

        // Terminados este mes
        $terminadosMes = Dictamen::where('estatus', 'TERMINADO')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Dictámenes negativos (alertas)
        $negativos = Dictamen::where('estatus', 'NEGATIVO')->count();

        return [
            Stat::make('Dictámenes Pendientes', $pendientes)
                ->description('Requieren atención inmediata')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
                // ->url(route('filament.admin.resources.juridico.dictamenes.index', [
                //     'tableFilters' => ['estatus' => ['value' => 'PENDIENTE']]
                // ])),

            Stat::make('En Investigación', $enProceso)
                ->description('Expedientes en proceso')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('warning'),

            Stat::make('Completados este Mes', $terminadosMes)
                ->description('Dictámenes finalizados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Dictámenes Negativos', $negativos)
                ->description('Propiedades no viables')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray'),
        ];
    }
}
