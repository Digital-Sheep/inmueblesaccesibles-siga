<?php

namespace App\Filament\Resources\Configuracion\CatSucursals;

use App\Filament\Clusters\Configuracion\ConfiguracionCluster;
use App\Filament\Resources\Configuracion\CatSucursals\Pages\CreateCatSucursal;
use App\Filament\Resources\Configuracion\CatSucursals\Pages\EditCatSucursal;
use App\Filament\Resources\Configuracion\CatSucursals\Pages\ListCatSucursals;
use App\Filament\Resources\Configuracion\CatSucursals\Pages\ViewCatSucursal;
use App\Filament\Resources\Configuracion\CatSucursals\Schemas\CatSucursalForm;
use App\Filament\Resources\Configuracion\CatSucursals\Schemas\CatSucursalInfolist;
use App\Filament\Resources\Configuracion\CatSucursals\Tables\CatSucursalsTable;
use App\Models\CatSucursal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CatSucursalResource extends Resource
{
    protected static ?string $model = CatSucursal::class;
    protected static ?string $modelLabel = 'Sucursal';
    protected static ?string $pluralModelLabel = 'Sucursales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $cluster = ConfiguracionCluster::class;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return CatSucursalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatSucursalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatSucursalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatSucursals::route('/'),
            'create' => CreateCatSucursal::route('/create'),
            'view' => ViewCatSucursal::route('/{record}'),
            'edit' => EditCatSucursal::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
