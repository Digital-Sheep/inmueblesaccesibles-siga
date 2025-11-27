<?php

namespace App\Filament\Resources\Juridico\ExpedienteJuridicos\Pages;

use App\Filament\Resources\Juridico\ExpedienteJuridicos\ExpedienteJuridicoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExpedienteJuridico extends ViewRecord
{
    protected static string $resource = ExpedienteJuridicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
