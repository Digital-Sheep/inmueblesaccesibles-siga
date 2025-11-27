<?php

namespace App\Filament\Resources\Comercial\Propiedades\Pages;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPropiedad extends ViewRecord
{
    protected static string $resource = PropiedadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
