<?php

namespace App\Filament\Resources\Configuracion\CatAdministradoras;

use App\Filament\Clusters\Configuracion\ConfiguracionCluster;
use App\Filament\Resources\Configuracion\CatAdministradoras\Pages\CreateCatAdministradora;
use App\Filament\Resources\Configuracion\CatAdministradoras\Pages\EditCatAdministradora;
use App\Filament\Resources\Configuracion\CatAdministradoras\Pages\ListCatAdministradoras;
use App\Filament\Resources\Configuracion\CatAdministradoras\Pages\ViewCatAdministradora;
use App\Filament\Resources\Configuracion\CatAdministradoras\Schemas\CatAdministradoraForm;
use App\Filament\Resources\Configuracion\CatAdministradoras\Schemas\CatAdministradoraInfolist;
use App\Filament\Resources\Configuracion\CatAdministradoras\Tables\CatAdministradorasTable;
use App\Models\CatAdministradora;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CatAdministradoraResource extends Resource
{
    protected static ?string $model = CatAdministradora::class;
    protected static ?string $modelLabel = 'Administradora';
    protected static ?string $pluralModelLabel = 'Administradoras';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = ConfiguracionCluster::class;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return CatAdministradoraForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CatAdministradoraInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatAdministradorasTable::configure($table);
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
            'index' => ListCatAdministradoras::route('/'),
            'create' => CreateCatAdministradora::route('/create'),
            'view' => ViewCatAdministradora::route('/{record}'),
            'edit' => EditCatAdministradora::route('/{record}/edit'),
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
