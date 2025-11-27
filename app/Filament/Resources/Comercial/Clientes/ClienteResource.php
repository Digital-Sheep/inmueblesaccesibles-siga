<?php

namespace App\Filament\Resources\Comercial\Clientes;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Clientes\Pages\CreateCliente;
use App\Filament\Resources\Comercial\Clientes\Pages\EditCliente;
use App\Filament\Resources\Comercial\Clientes\Pages\ListClientes;
use App\Filament\Resources\Comercial\Clientes\Pages\ViewCliente;
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
use UnitEnum;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Cartera de Clientes';
    protected static ?string $navigationLabel = 'Mis Clientes';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nombre_completo_virtual';

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
            'create' => CreateCliente::route('/create'),
            'view' => ViewCliente::route('/{record}'),
            'edit' => EditCliente::route('/{record}/edit'),
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
