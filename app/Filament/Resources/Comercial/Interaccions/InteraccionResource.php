<?php

namespace App\Filament\Resources\Comercial\Interaccions;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Interaccions\Pages\ListInteraccions;
use App\Filament\Resources\Comercial\Interaccions\Schemas\InteraccionForm;
use App\Filament\Resources\Comercial\Interaccions\Schemas\InteraccionInfolist;
use App\Filament\Resources\Comercial\Interaccions\Tables\InteraccionsTable;
use App\Models\Interaccion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class InteraccionResource extends Resource
{
    protected static ?string $model = Interaccion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $modelLabel = 'Interacción';
    protected static ?string $pluralModelLabel = 'Bitácora de Seguimiento';
    protected static ?string $navigationLabel = 'Seguimiento (Bitácora)';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'tipo';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('menu_interacciones');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('interacciones_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return InteraccionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InteraccionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InteraccionsTable::configure($table);
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
            'index' => ListInteraccions::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // private static function aplicarFiltrosDeSeguridad(Builder $query): Builder
    // {
    //     /** @var \App\Models\User $user */
    //     $user = Auth::user();

    //     // Si tiene permiso de ver TODOS los prospectos
    //     if ($user->can('interacciones_ver_todas')) {
    //         return $query;
    //     }

    //     // Filtro por sucursal
    //     if ($user->sucursal_id !== null) {
    //         $query->where('sucursal_id', $user->sucursal_id);
    //     }

    //     // Si tiene permiso de ver la sucursal completa, no filtramos por usuario
    //     if (!$user->can('prospectos_ver_sucursal_completa')) {
    //         $query->where('usuario_responsable_id', $user->id);
    //     }

    //     return $query;
    // }

    // public static function getEloquentQuery(): Builder
    // {
    //     $query = parent::getEloquentQuery();
    //     return self::aplicarFiltrosDeSeguridad($query);
    // }

    // public static function getGlobalSearchEloquentQuery(): Builder
    // {
    //     $query = parent::getGlobalSearchEloquentQuery();
    //     return self::aplicarFiltrosDeSeguridad($query);
    // }
}
