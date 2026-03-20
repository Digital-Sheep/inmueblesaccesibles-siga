<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\CreateSeguimientoNotaria;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\EditSeguimientoNotaria;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\ListSeguimientoNotarias;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Pages\ViewSeguimientoNotaria;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Schemas\SeguimientoNotariaForm;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Tables\SeguimientosNotariaTable;
use App\Models\SeguimientoNotaria;
use BackedEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SeguimientoNotariaResource extends Resource
{
    protected static ?string $model = SeguimientoNotaria::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = JuridicoCluster::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Seguimiento de Notarías';

    protected static ?string $modelLabel = 'Seguimiento de Notaría';

    protected static ?string $pluralModelLabel = 'Seguimiento de Notarías';

    protected static ?string $recordTitleAttribute = 'titulo';

    // ── Autorización ───────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientonotarias_ver');
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientonotarias_crear');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientonotarias_editar');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('seguimientonotarias_eliminar');
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
        return $schema->components(SeguimientoNotariaForm::schema());
    }

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
                        TextEntry::make('administradora_label')
                            ->label('Administradora')
                            ->getStateUsing(fn($record) => $record->nombre_administradora ?? '—'),
                        IconEntry::make('activo')->label('Activo')->boolean(),
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
                        ->visible(function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->can('seguimientonotarias_ver_todos');
                        }),
                ]),
        ]);
    }

    // ── Table ──────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return SeguimientosNotariaTable::configure($table);
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
