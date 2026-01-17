<?php

namespace App\Filament\Resources\Finanzas\ProcesoCompras;

use App\Filament\Clusters\Finanzas\FinanzasCluster;
use App\Filament\Resources\Finanzas\ProcesoCompras\Pages\CreateProcesoCompra;
use App\Filament\Resources\Finanzas\ProcesoCompras\Pages\EditProcesoCompra;
use App\Filament\Resources\Finanzas\ProcesoCompras\Pages\ListProcesoCompras;
use App\Filament\Resources\Finanzas\ProcesoCompras\Pages\ViewProcesoCompra;
use App\Filament\Resources\Finanzas\ProcesoCompras\Schemas\ProcesoCompraForm;
use App\Filament\Resources\Finanzas\ProcesoCompras\Schemas\ProcesoCompraInfolist;
use App\Filament\Resources\Finanzas\ProcesoCompras\Tables\ProcesoComprasTable;

use App\Models\ProcesoCompra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProcesoCompraResource extends Resource
{
    protected static ?string $model = ProcesoCompra::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = FinanzasCluster::class;

    public static function form(Schema $schema): Schema
    {
        return ProcesoCompraForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProcesoCompraInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProcesoComprasTable::configure($table);
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
            'index' => ListProcesoCompras::route('/'),
            'create' => CreateProcesoCompra::route('/create'),
            'view' => ViewProcesoCompra::route('/{record}'),
            'edit' => EditProcesoCompra::route('/{record}/edit'),
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
