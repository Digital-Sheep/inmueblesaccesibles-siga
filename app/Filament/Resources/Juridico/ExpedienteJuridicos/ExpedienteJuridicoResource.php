<?php

namespace App\Filament\Resources\Juridico\ExpedienteJuridicos;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\ExpedienteJuridicos\Pages\CreateExpedienteJuridico;
use App\Filament\Resources\Juridico\ExpedienteJuridicos\Pages\EditExpedienteJuridico;
use App\Filament\Resources\Juridico\ExpedienteJuridicos\Pages\ListExpedienteJuridicos;
use App\Filament\Resources\Juridico\ExpedienteJuridicos\Pages\ViewExpedienteJuridico;
use App\Filament\Resources\Juridico\ExpedienteJuridicos\Schemas\ExpedienteJuridicoForm;
use App\Filament\Resources\Juridico\ExpedienteJuridicos\Schemas\ExpedienteJuridicoInfolist;
use App\Filament\Resources\Juridico\ExpedienteJuridicos\Tables\ExpedienteJuridicosTable;
use App\Models\ExpedienteJuridico;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ExpedienteJuridicoResource extends Resource
{
    protected static ?string $model = ExpedienteJuridico::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?string $recordTitleAttribute = 'codigo_expediente';

    public static function form(Schema $schema): Schema
    {
        return ExpedienteJuridicoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpedienteJuridicoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpedienteJuridicosTable::configure($table);
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
            'index' => ListExpedienteJuridicos::route('/'),
            'create' => CreateExpedienteJuridico::route('/create'),
            'view' => ViewExpedienteJuridico::route('/{record}'),
            'edit' => EditExpedienteJuridico::route('/{record}/edit'),
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
