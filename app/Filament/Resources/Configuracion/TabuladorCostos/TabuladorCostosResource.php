<?php

namespace App\Filament\Resources\Configuracion\TabuladorCostos;

use App\Filament\Clusters\Configuracion\ConfiguracionCluster;
use App\Filament\Resources\Configuracion\TabuladorCostos\Pages\ListTabuladorCostos;
use App\Filament\Resources\Configuracion\TabuladorCostos\Schemas\TabuladorCostosForm;
use App\Filament\Resources\Configuracion\TabuladorCostos\Tables\TabuladorCostosTable;
use App\Models\CatTabuladorCosto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TabuladorCostosResource extends Resource
{
    protected static ?string $model = CatTabuladorCosto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Tabulador de costos';

    protected static ?string $modelLabel = 'Tabulador de costo';

    protected static ?string $pluralModelLabel = 'Tabulador de costos';

    protected static ?string $cluster = ConfiguracionCluster::class;

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('configuracion_ver');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('configuracion_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return TabuladorCostosForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TabuladorCostosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTabuladorCostos::route('/'),
        ];
    }
}
