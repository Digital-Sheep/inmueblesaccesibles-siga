<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\ProcesoVentas\Pages\CreateProcesoVenta;
use App\Filament\Resources\Comercial\ProcesoVentas\Pages\EditProcesoVenta;
use App\Filament\Resources\Comercial\ProcesoVentas\Pages\ListProcesoVentas;
use App\Filament\Resources\Comercial\ProcesoVentas\Pages\ViewProcesoVenta;
use App\Filament\Resources\Comercial\ProcesoVentas\Schemas\ProcesoVentaForm;
use App\Filament\Resources\Comercial\ProcesoVentas\Schemas\ProcesoVentaInfolist;
use App\Filament\Resources\Comercial\ProcesoVentas\Tables\ProcesoVentasTable;
use App\Models\Cliente;
use App\Models\ProcesoVenta;
use App\Models\Prospecto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProcesoVentaResource extends Resource
{
    protected static ?string $model = ProcesoVenta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $modelLabel = 'Proceso de Venta';
    protected static ?string $pluralModelLabel = 'Seguimiento de Ventas';
    protected static ?string $navigationLabel = 'Procesos de Venta';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'folio_apartado';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('menu_ventas');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('ventas_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return ProcesoVentaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProcesoVentaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProcesoVentasTable::configure($table);
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
            'index' => ListProcesoVentas::route('/'),
            'view' => ViewProcesoVenta::route('/{record}'),
        ];
    }

    public static function getRecordTitle($record): string
    {
        return "Proceso de Venta #{$record->id}";
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

        if ($user->can('ventas_ver_todas')) {
            return $query;
        }

        if ($user->sucursal_id !== null) {
            $query->whereHasMorph('interesado', [Prospecto::class, Cliente::class], function ($q) use ($user) {
                $q->where('sucursal_id', $user->sucursal_id);
            });

            if (!$user->can('ventas_ver_sucursal_completa')) {
                $query->where('vendedor_id', $user->id);
            }

            return $query;
        }

        return $query->where('vendedor_id', $user->id);
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
}
