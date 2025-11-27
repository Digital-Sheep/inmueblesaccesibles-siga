<?php

namespace App\Filament\Resources\Juridico\SolicitudContratos\Pages;

use App\Filament\Resources\Juridico\SolicitudContratos\SolicitudContratoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudContratos extends ListRecords
{
    protected static string $resource = SolicitudContratoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
