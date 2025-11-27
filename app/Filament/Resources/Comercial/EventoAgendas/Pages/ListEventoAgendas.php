<?php

namespace App\Filament\Resources\Comercial\EventoAgendas\Pages;

use App\Filament\Resources\Comercial\EventoAgendas\EventoAgendaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventoAgendas extends ListRecords
{
    protected static string $resource = EventoAgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
