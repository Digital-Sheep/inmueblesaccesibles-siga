<?php

namespace App\Filament\Resources\Comercial\Prospectos;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\ProspectoResource\RelationManagers\InteraccionesRelationManager;
use App\Filament\Resources\Comercial\Prospectos\Pages\CreateProspecto;
use App\Filament\Resources\Comercial\Prospectos\Pages\EditProspecto;
use App\Filament\Resources\Comercial\Prospectos\Pages\ListProspectos;
use App\Filament\Resources\Comercial\Prospectos\Pages\ViewProspecto;
use App\Filament\Resources\Comercial\Prospectos\Schemas\ProspectoForm;
use App\Filament\Resources\Comercial\Prospectos\Schemas\ProspectoInfolist;
use App\Filament\Resources\Comercial\Prospectos\Tables\ProspectosTable;
use App\Models\Prospecto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProspectoResource extends Resource
{
    protected static ?string $model = Prospecto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Prospecto';
    protected static ?string $pluralModelLabel = 'Prospectos';
    protected static ?string $navigationLabel = 'Mis Prospectos';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nombre_completo';

    public static function form(Schema $schema): Schema
    {
        return ProspectoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProspectoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProspectosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InteraccionesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProspectos::route('/'),
            'create' => CreateProspecto::route('/create'),
            'view' => ViewProspecto::route('/{record}'),
            'edit' => EditProspecto::route('/{record}/edit'),
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
