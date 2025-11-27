<?php

namespace App\Filament\Resources\Comercial\EventoAgendas\Pages;

use App\Filament\Resources\Comercial\EventoAgendas\EventoAgendaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventoAgenda extends ViewRecord
{
    protected static string $resource = EventoAgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
