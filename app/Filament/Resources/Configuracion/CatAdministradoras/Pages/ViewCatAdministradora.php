<?php

namespace App\Filament\Resources\Configuracion\CatAdministradoras\Pages;

use App\Filament\Resources\Configuracion\CatAdministradoras\CatAdministradoraResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCatAdministradora extends ViewRecord
{
    protected static string $resource = CatAdministradoraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
