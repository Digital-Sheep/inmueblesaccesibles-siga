<?php

namespace App\Filament\Resources\Juridico\SolicitudContratos\Pages;

use App\Filament\Resources\Juridico\SolicitudContratos\SolicitudContratoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSolicitudContrato extends ViewRecord
{
    protected static string $resource = SolicitudContratoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
