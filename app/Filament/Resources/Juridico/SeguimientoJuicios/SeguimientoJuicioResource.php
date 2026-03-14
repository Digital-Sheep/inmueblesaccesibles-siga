<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios;

use App\Enums\NivelPrioridadJuicioEnum;
use App\Enums\SedeJuicioEnum;
use App\Enums\TipoProcesoJuicioEnum;
use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\CreateSeguimientoJuicio;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\EditSeguimientoJuicio;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\ListSeguimientoJuicios;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Pages\ViewSeguimientoJuicio;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Schemas\SeguimientoJuicioForm;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Schemas\SeguimientoJuicioInfolist;
use App\Models\SeguimientoJuicio;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeguimientoJuicioResource extends Resource
{
    protected static ?string $model = SeguimientoJuicio::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $cluster = JuridicoCluster::class;


    protected static ?string $navigationLabel = 'Seguimiento de Juicios';

    protected static ?string $modelLabel = 'Seguimiento de Juicio';

    protected static ?string $pluralModelLabel = 'Seguimiento de Juicios';

    protected static ?string $recordTitleAttribute = 'titulo';

    // ── Autorización ───────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()->can('seguimientojuicios_ver');
    }

    // public static function canCreate(): bool
    // {
    //     return auth()->user()->can('seguimientojuicios_crear');
    // }

    // public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    // {
    //     return auth()->user()->can('seguimientojuicios_editar');
    // }

    // public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    // {
    //     return auth()->user()->can('seguimientojuicios_eliminar');
    // }

    // ── Query base ─────────────────────────────────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['propiedad', 'actuaciones'])
            ->withCount('actuaciones');
    }

    // ── Form ───────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components(SeguimientoJuicioForm::schema());
    }

    // ── Infolist ───────────────────────────────────────────────────────────────

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(SeguimientoJuicioInfolist::schema());
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nivel_prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->sortable(),

                TextColumn::make('id_garantia')
                    ->label('ID Garantía')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                TextColumn::make('numero_credito')
                    ->label('Núm. Crédito')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('nombre_cliente')
                    ->label('Cliente')
                    ->searchable()
                    ->default('Sin cliente')
                    ->limit(30),

                TextColumn::make('sede')
                    ->label('Sede')
                    ->badge()
                    ->sortable(),

                TextColumn::make('abogado_nombre')
                    ->label('Abogado')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('etapa_actual')
                    ->label('Etapa Actual')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->etapa_actual)
                    ->toggleable(),

                TextColumn::make('actuaciones_count')
                    ->label('Actuaciones')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                IconColumn::make('sin_demanda')
                    ->label('Sin Demanda')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Últ. actualización')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sede')
                    ->label('Sede')
                    ->options(SedeJuicioEnum::class)
                    ->multiple(),

                SelectFilter::make('nivel_prioridad')
                    ->label('Prioridad')
                    ->options(NivelPrioridadJuicioEnum::class)
                    ->multiple(),

                SelectFilter::make('tipo_proceso')
                    ->label('Tipo de Proceso')
                    ->options(TipoProcesoJuicioEnum::class),

                TernaryFilter::make('sin_demanda')
                    ->label('Sin Demanda'),

                TernaryFilter::make('activo')
                    ->label('Solo Activos')
                    ->default(true),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nivel_prioridad', 'asc')
            ->emptyStateHeading('Sin juicios registrados')
            ->emptyStateDescription('Agrega el primer seguimiento de juicio.')
            ->emptyStateIcon('heroicon-o-scale');
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
