<?php

namespace App\Filament\Resources\Configuracion\CatSucursals\Pages;

use App\Filament\Resources\Configuracion\CatSucursals\CatSucursalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCatSucursal extends ViewRecord
{
    protected static string $resource = CatSucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
