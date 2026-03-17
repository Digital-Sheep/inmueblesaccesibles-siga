<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\CreateSeguimientoJuicio;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\EditSeguimientoJuicio;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\ListSeguimientoJuicios;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\ViewSeguimientoJuicio;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Schemas\SeguimientoJuicioForm;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Schemas\SeguimientoJuicioInfolist;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Tables\SeguimientosJuicioTable;
use App\Models\SeguimientoJuicio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SeguimientoJuicioResource extends Resource
{
    protected static ?string $model = SeguimientoJuicio::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Seguimiento de Juicios';

    protected static ?string $modelLabel = 'Seguimiento de Juicio';

    protected static ?string $pluralModelLabel = 'Seguimiento de Juicios';

    protected static ?string $recordTitleAttribute = 'titulo';

    // ── Autorización ───────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientojuicios_ver');
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientojuicios_crear');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientojuicios_editar');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientojuicios_eliminar');
    }

    // ── Query base ─────────────────────────────────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['propiedad', 'actuaciones'])
            ->withCount('actuaciones');
    }

    // ── Schemas ────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components(SeguimientoJuicioForm::schema());
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(SeguimientoJuicioInfolist::schema());
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return SeguimientosJuicioTable::configure($table);
    }

    // ── Relation Managers ──────────────────────────────────────────────────────

    public static function getRelationManagers(): array
    {
        return [
            ActuacionesJuicioRelationManager::class,
        ];
    }

    // ── Pages ──────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => ListSeguimientoJuicios::route('/'),
            'create' => CreateSeguimientoJuicio::route('/create'),
            'view'   => ViewSeguimientoJuicio::route('/{record}'),
            'edit'   => EditSeguimientoJuicio::route('/{record}/edit'),
        ];
    }
}
