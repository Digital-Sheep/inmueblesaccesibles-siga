<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ;

use App\Enums\TipoProcesoDictamenEnum;
use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\ActuacionesDictamenRelationManager;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\Schemas\SeguimientoDictamenForm;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\Schemas\SeguimientoDictamenInfolist;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\Tables\SeguimientosDictamenTable;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages\CreateSeguimientoDictamenURRJ;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages\EditSeguimientoDictamenURRJ;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages\ListSeguimientosDictamenURRJ;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages\ViewSeguimientoDictamenURRJ;
use App\Models\SeguimientoDictamen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeguimientoDictamenURRJResource extends Resource
{
    protected static ?string $model = SeguimientoDictamen::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;
    protected static ?string $cluster = JuridicoCluster::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Seguimiento Dictámenes URRJ';
    protected static ?string $modelLabel = 'Dictamen URRJ';
    protected static ?string $pluralModelLabel = 'Dictámenes URRJ';
    protected static ?string $recordTitleAttribute = 'titulo';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('seguimientodictamenes_ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('seguimientodictamenes_crear');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->can('seguimientodictamenes_editar');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()->can('seguimientodictamenes_eliminar');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['propiedad', 'cliente', 'solicitante', 'catAdministradora', 'actuaciones'])
            ->withCount('actuaciones')
            ->whereIn('tipo_proceso', [
                TipoProcesoDictamenEnum::CAMBIO->value,
                TipoProcesoDictamenEnum::INVERSION->value,
            ]);;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(SeguimientoDictamenForm::schema());
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(SeguimientoDictamenInfolist::schema());
    }

    public static function table(Table $table): Table
    {
        return SeguimientosDictamenTable::configure($table, static::class);
    }

    public static function getRelationManagers(): array
    {
        return [ActuacionesDictamenRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSeguimientosDictamenURRJ::route('/'),
            'create' => CreateSeguimientoDictamenURRJ::route('/create'),
            'view'   => ViewSeguimientoDictamenURRJ::route('/{record}'),
            'edit'   => EditSeguimientoDictamenURRJ::route('/{record}/edit'),
        ];
    }
}
