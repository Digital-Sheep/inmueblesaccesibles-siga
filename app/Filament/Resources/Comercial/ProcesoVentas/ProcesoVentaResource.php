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
use App\Models\ProcesoVenta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
            'create' => CreateProcesoVenta::route('/create'),
            'view' => ViewProcesoVenta::route('/{record}'),
            'edit' => EditProcesoVenta::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
