<?php

namespace App\Filament\Resources\Juridico\Dictamens;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\Dictamens\Pages\CreateDictamen;
use App\Filament\Resources\Juridico\Dictamens\Pages\EditDictamen;
use App\Filament\Resources\Juridico\Dictamens\Pages\ListDictamens;
use App\Filament\Resources\Juridico\Dictamens\Pages\ViewDictamen;
use App\Filament\Resources\Juridico\Dictamens\Schemas\DictamenForm;
use App\Filament\Resources\Juridico\Dictamens\Schemas\DictamenInfolist;
use App\Filament\Resources\Juridico\Dictamens\Tables\DictamensTable;
use App\Models\Dictamen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DictamenResource extends Resource
{
    protected static ?string $model = Dictamen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?string $recordTitleAttribute = 'numero_credito';

    public static function getPluralModelLabel(): string
    {
        return 'Dictámenes';
    }

    public static function form(Schema $schema): Schema
    {
        return DictamenForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DictamenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DictamensTable::configure($table);
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
            'index' => ListDictamens::route('/'),
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
