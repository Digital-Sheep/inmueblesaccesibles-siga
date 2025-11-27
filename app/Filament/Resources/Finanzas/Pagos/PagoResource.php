<?php

namespace App\Filament\Resources\Finanzas\Pagos;

use App\Filament\Clusters\Finanzas\FinanzasCluster;
use App\Filament\Resources\Finanzas\Pagos\Pages\CreatePago;
use App\Filament\Resources\Finanzas\Pagos\Pages\EditPago;
use App\Filament\Resources\Finanzas\Pagos\Pages\ListPagos;
use App\Filament\Resources\Finanzas\Pagos\Pages\ViewPago;
use App\Filament\Resources\Finanzas\Pagos\Schemas\PagoForm;
use App\Filament\Resources\Finanzas\Pagos\Schemas\PagoInfolist;
use App\Filament\Resources\Finanzas\Pagos\Tables\PagosTable;
use App\Models\Pago;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PagoResource extends Resource
{
    protected static ?string $model = Pago::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Pago';
    protected static ?string $pluralModelLabel = 'Control de Ingresos';
    protected static ?string $navigationLabel = 'Pagos y Validación';

    protected static ?string $cluster = FinanzasCluster::class;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PagoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PagoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagosTable::configure($table);
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
            'index' => ListPagos::route('/'),
            'create' => CreatePago::route('/create'),
            'view' => ViewPago::route('/{record}'),
            'edit' => EditPago::route('/{record}/edit'),
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
