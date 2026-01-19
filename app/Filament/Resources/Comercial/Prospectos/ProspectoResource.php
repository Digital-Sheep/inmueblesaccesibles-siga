<?php

namespace App\Filament\Resources\Comercial\Prospectos;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Prospectos\Pages\ListProspectos;
use App\Filament\Resources\Comercial\Prospectos\Schemas\ProspectoForm;
use App\Filament\Resources\Comercial\Prospectos\Schemas\ProspectoInfolist;
use App\Filament\Resources\Comercial\Prospectos\Tables\ProspectosTable;
use App\Models\Prospecto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProspectoResource extends Resource
{
    protected static ?string $model = Prospecto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Prospecto';
    protected static ?string $pluralModelLabel = 'Prospectos';
    protected static ?string $navigationLabel = 'Mis Prospectos';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nombre_completo';

    public static function form(Schema $schema): Schema
    {
        return ProspectoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProspectoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProspectosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProspectos::route('/'),
        ];
    }

    private static function aplicarFiltrosDeSeguridad(Builder $query): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->can('gestionar_toda_la_red')) {
            return $query;
        }

        if ($user->can('gestionar_sucursal_propia')) {
            return $query->where('sucursal_id', $user->sucursal_id);
        }

        return $query->where('usuario_responsable_id', $user->id);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->soloProspectos();
        return self::aplicarFiltrosDeSeguridad($query);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $query = parent::getGlobalSearchEloquentQuery()->soloProspectos();
        return self::aplicarFiltrosDeSeguridad($query);
    }
}
