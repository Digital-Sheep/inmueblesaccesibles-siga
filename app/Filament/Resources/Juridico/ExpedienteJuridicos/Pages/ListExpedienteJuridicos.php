<?php

namespace App\Filament\Resources\Juridico\ExpedienteJuridicos\Pages;

use App\Filament\Resources\Juridico\ExpedienteJuridicos\ExpedienteJuridicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExpedienteJuridicos extends ListRecords
{
    protected static string $resource = ExpedienteJuridicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
