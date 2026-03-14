<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias;

use App\Enums\SedeJuicioEnum;
use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\CreateSeguimientoNotaria;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\EditSeguimientoNotaria;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\ListSeguimientoNotarias;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\ViewSeguimientoNotaria;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Schemas\SeguimientoNotariaForm;
use App\Models\SeguimientoNotaria;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeguimientoNotariaResource extends Resource
{
    protected static ?string $model = SeguimientoNotaria::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?string $navigationLabel = 'Seguimiento de Notarías';

    protected static ?string $modelLabel = 'Seguimiento de Notaría';

    protected static ?string $pluralModelLabel = 'Seguimiento de Notarías';

    protected static ?string $recordTitleAttribute = 'titulo';

    // ── Autorización ───────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()->can('seguimientonotarias_ver');
    }

    // public static function canCreate(): bool
    // {
    //     return auth()->user()->can('seguimientonotarias_crear');
    // }

    // public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    // {
    //     return auth()->user()->can('seguimientonotarias_editar');
    // }

    // public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    // {
    //     return auth()->user()->can('seguimientonotarias_eliminar');
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
        return $schema->components(SeguimientoNotariaForm::schema());
    }

    // ── Infolist ───────────────────────────────────────────────────────────────

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificación')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('id_garantia')->label('ID Garantía')->default('—'),
                        TextEntry::make('numero_credito')->label('Núm. Crédito')->default('—'),
                        TextEntry::make('nombre_cliente')->label('Cliente')->default('Sin cliente'),
                        TextEntry::make('sede')->label('Sede')->badge(),
                        TextEntry::make('notario')->label('Notario')->default('—'),
                        TextEntry::make('numero_escritura')->label('Núm. Escritura')->default('—'),
                        TextEntry::make('fecha_escritura')->label('Fecha Escritura')->date('d/m/Y')->default('—'),
                        TextEntry::make('administradora')->label('Administradora')->default('—'),
                        IconEntry::make('activo')->label('Activo')->boolean(),
                    ]),
                ]),

            Section::make('Seguimiento')
                ->schema([
                    TextEntry::make('etapa_actual')
                        ->label('Etapa Actual')
                        ->default('Sin información')
                        ->columnSpanFull(),
                    TextEntry::make('notas_director')
                        ->label('Notas Director / UCP')
                        ->default('—')
                        ->columnSpanFull()
                        ->visible(fn() => auth()->user()->can('seguimientonotarias_ver_todos')),
                ]),
        ]);
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_garantia')
                    ->label('ID Garantía')
                    ->searchable()
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

                TextColumn::make('notario')
                    ->label('Notario')
                    ->limit(25)
                    ->default('—'),

                TextColumn::make('numero_escritura')
                    ->label('Escritura')
                    ->default('—'),

                TextColumn::make('fecha_escritura')
                    ->label('Fecha Escritura')
                    ->date('d/m/Y')
                    ->default('—'),

                TextColumn::make('actuaciones_count')
                    ->label('Actuaciones')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sede')
                    ->label('Sede')
                    ->options(SedeJuicioEnum::class)
                    ->multiple(),

                TernaryFilter::make('activo')
                    ->label('Solo Activos')
                    ->default(true),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Sin seguimientos de notarías')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    // ── Relation Managers ──────────────────────────────────────────────────────

    public static function getRelationManagers(): array
    {
        return [
            ActuacionesNotariaRelationManager::class,
        ];
    }

    // ── Pages ──────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => ListSeguimientoNotarias::route('/'),
            'create' => CreateSeguimientoNotaria::route('/create'),
            'view'   => ViewSeguimientoNotaria::route('/{record}'),
            'edit'   => EditSeguimientoNotaria::route('/{record}/edit'),
        ];
    }
}
