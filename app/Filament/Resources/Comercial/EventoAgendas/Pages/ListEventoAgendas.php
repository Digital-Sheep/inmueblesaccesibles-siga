<?php

namespace App\Filament\Resources\Comercial\EventoAgendas\Pages;

use App\Filament\Resources\Comercial\EventoAgendas\EventoAgendaResource;
use App\Filament\Widgets\AgendaComercialWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventoAgendas extends ListRecords
{
    protected static string $resource = EventoAgendaResource::class;
    protected string $view = 'filament.pages.agenda-full';

    public function getTitle(): string
    {
        return 'Agenda Comercial';
    }

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AgendaComercialWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
