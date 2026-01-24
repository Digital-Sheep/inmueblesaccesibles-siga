<?php

namespace App\Filament\Resources\Configuracion\Roles;

use App\Filament\Clusters\Configuracion\ConfiguracionCluster;
use App\Filament\Resources\Configuracion\Roles\Pages\ListRoles;
use App\Filament\Resources\Configuracion\Roles\Pages\ViewRol;
use App\Filament\Resources\Configuracion\Roles\Schemas\RolForm;
use App\Filament\Resources\Configuracion\Roles\Schemas\RolInfolist;
use App\Filament\Resources\Configuracion\Roles\Tables\RolesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use BackedEnum;

class RolResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'Rol';
    protected static ?string $pluralModelLabel = 'Roles';
    protected static ?string $navigationLabel = 'Roles y permisos';

    protected static ?string $cluster = ConfiguracionCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('menu_roles');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('roles_ver');
    }

    public static function canView($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('roles_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return RolForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RolInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'view' => ViewRol::route('/{record}'),
        ];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return "Rol: {$record->name}";
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
