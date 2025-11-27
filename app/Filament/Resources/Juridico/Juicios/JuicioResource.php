<?php

namespace App\Filament\Resources\Juridico\Juicios;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\Juicios\Pages\CreateJuicio;
use App\Filament\Resources\Juridico\Juicios\Pages\EditJuicio;
use App\Filament\Resources\Juridico\Juicios\Pages\ListJuicios;
use App\Filament\Resources\Juridico\Juicios\Pages\ViewJuicio;
use App\Filament\Resources\Juridico\Juicios\Schemas\JuicioForm;
use App\Filament\Resources\Juridico\Juicios\Schemas\JuicioInfolist;
use App\Filament\Resources\Juridico\Juicios\Tables\JuiciosTable;
use App\Models\Juicio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class JuicioResource extends Resource
{
    protected static ?string $model = Juicio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?string $recordTitleAttribute = 'no_expediente_juzgado';

    public static function form(Schema $schema): Schema
    {
        return JuicioForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JuicioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JuiciosTable::configure($table);
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
            'index' => ListJuicios::route('/'),
            'create' => CreateJuicio::route('/create'),
            'view' => ViewJuicio::route('/{record}'),
            'edit' => EditJuicio::route('/{record}/edit'),
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
