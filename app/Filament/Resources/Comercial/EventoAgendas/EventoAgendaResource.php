<?php

namespace App\Filament\Resources\Comercial\EventoAgendas;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\EventoAgendas\Pages\CreateEventoAgenda;
use App\Filament\Resources\Comercial\EventoAgendas\Pages\EditEventoAgenda;
use App\Filament\Resources\Comercial\EventoAgendas\Pages\ListEventoAgendas;
use App\Filament\Resources\Comercial\EventoAgendas\Pages\ViewEventoAgenda;
use App\Filament\Resources\Comercial\EventoAgendas\Schemas\EventoAgendaForm;
use App\Filament\Resources\Comercial\EventoAgendas\Schemas\EventoAgendaInfolist;
use App\Filament\Resources\Comercial\EventoAgendas\Tables\EventoAgendasTable;
use App\Models\EventoAgenda;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EventoAgendaResource extends Resource
{
    protected static ?string $model = EventoAgenda::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $modelLabel = 'Evento';
    protected static ?string $pluralModelLabel = 'Agenda y Citas';
    protected static ?string $navigationLabel = 'Mi Agenda';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return EventoAgendaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventoAgendaInfolist::configure($schema);
    }

    // public static function table(Table $table): Table
    // {
    //     return EventoAgendasTable::configure($table);
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventoAgendas::route('/'),
            'create' => CreateEventoAgenda::route('/create'),
            'view' => ViewEventoAgenda::route('/{record}'),
            'edit' => EditEventoAgenda::route('/{record}/edit'),
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
