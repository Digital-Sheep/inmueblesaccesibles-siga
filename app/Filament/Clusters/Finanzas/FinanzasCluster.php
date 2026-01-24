<?php

namespace App\Filament\Clusters\Finanzas;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class FinanzasCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;

    // Nombre "Administrativo" para el menú en lugar de "Finanzas"
    protected static ?string $clusterBreadcrumb = 'Administrativo';
    protected static ?string $navigationLabel = 'Administrativo';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('menu_administrativo');
    }
}
