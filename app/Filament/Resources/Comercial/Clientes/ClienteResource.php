<?php

namespace App\Filament\Resources\Comercial\Clientes;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Clientes\Pages\ListClientes;
use App\Filament\Resources\Comercial\Clientes\Schemas\ClienteForm;
use App\Filament\Resources\Comercial\Clientes\Schemas\ClienteInfolist;
use App\Filament\Resources\Comercial\Clientes\Tables\ClientesTable;
use App\Models\Cliente;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Cartera de Clientes';
    protected static ?string $navigationLabel = 'Mis Clientes';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nombre_completo_virtual';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('menu_clientes');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('clientes_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return ClienteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClienteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientesTable::configure($table);
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
            'index' => ListClientes::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // protected function aplicarFiltrosDeSeguridad($query)
    // {
    //     $user = Auth::user();

    //     // Si tiene permiso de ver todos los clientes (nivel corporativo)
    //     if ($user->can('clientes_ver_todos')) {
    //         return $query;
    //     }

    //     // Si tiene permiso de ver toda su sucursal
    //     if ($user->can('clientes_ver_sucursal_completa') && $user->sucursal_id) {
    //         return $query->where('sucursal_id', $user->sucursal_id);
    //     }

    //     // Por defecto: solo ve sus propios clientes
    //     return $query->where('usuario_responsable_id', $user->id);
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
