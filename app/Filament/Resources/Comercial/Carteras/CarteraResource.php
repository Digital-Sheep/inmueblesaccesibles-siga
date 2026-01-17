<?php

namespace App\Filament\Resources\Comercial\Carteras;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Carteras\Pages\CreateCartera;
use App\Filament\Resources\Comercial\Carteras\Pages\EditCartera;
use App\Filament\Resources\Comercial\Carteras\Pages\ListCarteras;
use App\Filament\Resources\Comercial\Carteras\Pages\ViewCartera;
use App\Filament\Resources\Comercial\Carteras\Schemas\CarteraForm;
use App\Filament\Resources\Comercial\Carteras\Schemas\CarteraInfolist;
use App\Filament\Resources\Comercial\Carteras\Tables\CarterasTable;
use App\Models\Cartera;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CarteraResource extends Resource
{
    protected static ?string $model = Cartera::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static ?string $modelLabel = 'Cartera';
    protected static ?string $pluralModelLabel = 'Carga de Carteras';
    protected static ?string $navigationLabel = 'Cargar Cartera';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return CarteraForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CarteraInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarterasTable::configure($table);
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
            'index' => ListCarteras::route('/'),
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
