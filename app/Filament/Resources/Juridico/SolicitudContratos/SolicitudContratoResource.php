<?php

namespace App\Filament\Resources\Juridico\SolicitudContratos;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SolicitudContratos\Pages\CreateSolicitudContrato;
use App\Filament\Resources\Juridico\SolicitudContratos\Pages\EditSolicitudContrato;
use App\Filament\Resources\Juridico\SolicitudContratos\Pages\ListSolicitudContratos;
use App\Filament\Resources\Juridico\SolicitudContratos\Pages\ViewSolicitudContrato;
use App\Filament\Resources\Juridico\SolicitudContratos\Schemas\SolicitudContratoForm;
use App\Filament\Resources\Juridico\SolicitudContratos\Schemas\SolicitudContratoInfolist;
use App\Filament\Resources\Juridico\SolicitudContratos\Tables\SolicitudContratosTable;
use App\Models\SolicitudContrato;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SolicitudContratoResource extends Resource
{
    protected static ?string $model = SolicitudContrato::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SolicitudContratoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SolicitudContratoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SolicitudContratosTable::configure($table);
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
            'index' => ListSolicitudContratos::route('/'),
            'create' => CreateSolicitudContrato::route('/create'),
            'view' => ViewSolicitudContrato::route('/{record}'),
            'edit' => EditSolicitudContrato::route('/{record}/edit'),
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
