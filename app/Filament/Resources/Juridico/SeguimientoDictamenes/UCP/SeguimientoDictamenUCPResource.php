<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP;

use App\Enums\TipoProcesoDictamenEnum;
use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\ActuacionesDictamenRelationManager;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\Schemas\SeguimientoDictamenForm;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\Schemas\SeguimientoDictamenInfolist;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\Tables\SeguimientosDictamenTable;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages\CreateSeguimientoDictamenUCP;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages\EditSeguimientoDictamenUCP;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages\ListSeguimientosDictamenUCP;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages\ViewSeguimientoDictamenUCP;
use App\Models\SeguimientoDictamen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeguimientoDictamenUCPResource extends Resource
{
    protected static ?string $model = SeguimientoDictamen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Seguimiento Dictámenes UCP';

    protected static ?string $modelLabel = 'Dictamen UCP';

    protected static ?string $pluralModelLabel = 'Dictámenes UCP';

    protected static ?string $recordTitleAttribute = 'titulo';

    // ── Autorización ───────────────────────────────────────────────────────────

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

    // ── Query base ─────────────────────────────────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['propiedad', 'cliente', 'solicitante', 'catAdministradora', 'actuaciones'])
            ->withCount('actuaciones')
            ->where('tipo_proceso', TipoProcesoDictamenEnum::VENTA);;
    }

    // ── Schemas ────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components(SeguimientoDictamenForm::schema());
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(SeguimientoDictamenInfolist::schema());
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return SeguimientosDictamenTable::configure($table, static::class);
    }

    // ── Relation Managers ──────────────────────────────────────────────────────

    public static function getRelationManagers(): array
    {
        return [
            ActuacionesDictamenRelationManager::class,
        ];
    }

    // ── Pages ──────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => ListSeguimientosDictamenUCP::route('/'),
            'create' => CreateSeguimientoDictamenUCP::route('/create'),
            'view'   => ViewSeguimientoDictamenUCP::route('/{record}'),
            'edit'   => EditSeguimientoDictamenUCP::route('/{record}/edit'),
        ];
    }
}
