<?php

namespace App\Filament\Resources\Comercial\Propiedades;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Propiedades\Pages\CreatePropiedad;
use App\Filament\Resources\Comercial\Propiedades\Pages\EditPropiedad;
use App\Filament\Resources\Comercial\Propiedades\Pages\ListPropiedades;
use App\Filament\Resources\Comercial\Propiedades\Pages\ViewPropiedad;
use App\Filament\Resources\Comercial\Propiedades\Schemas\PropiedadForm;
use App\Filament\Resources\Comercial\Propiedades\Schemas\PropiedadInfolist;
use App\Filament\Resources\Comercial\Propiedades\Tables\PropiedadesTable;

use App\Models\Propiedad;

use BackedEnum;
use UnitEnum;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PropiedadResource extends Resource
{
    protected static ?string $model = Propiedad::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;
    protected static ?string $navigationLabel = 'Propiedades';
    protected static ?string $modelLabel = 'Propiedad';
    protected static ?string $pluralModelLabel = 'Propiedades';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'numero_credito';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('menu_propiedades');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('propiedades_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return PropiedadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PropiedadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropiedadesTable::configure($table);
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
            'index' => ListPropiedades::route('/'),
            // 'view' => ViewPropiedad::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    private static function aplicarFiltrosDeSeguridad(Builder $query): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->can('propiedades_ver_todos')) {
            $query->where('estatus_comercial', '!=', 'BORRADOR');
        }

        return $query;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        return self::aplicarFiltrosDeSeguridad($query);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        return self::aplicarFiltrosDeSeguridad($query);
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'id';
    }
}
