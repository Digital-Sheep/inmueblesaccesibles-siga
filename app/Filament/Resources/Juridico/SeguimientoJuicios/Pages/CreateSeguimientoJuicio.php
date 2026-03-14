<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Pages;

use App\Filament\Resources\Juridico\SeguimientoJuicios\SeguimientoJuicioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSeguimientoJuicio extends CreateRecord
{
    protected static string $resource = SeguimientoJuicioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
