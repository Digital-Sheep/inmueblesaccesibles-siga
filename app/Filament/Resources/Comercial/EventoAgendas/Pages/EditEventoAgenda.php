<?php

namespace App\Filament\Resources\Comercial\EventoAgendas\Pages;

use App\Filament\Resources\Comercial\EventoAgendas\EventoAgendaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEventoAgenda extends EditRecord
{
    protected static string $resource = EventoAgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
