<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Pages;

use App\Filament\Resources\Comercial\ProcesoVentas\ProcesoVentaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProcesoVenta extends ViewRecord
{
    protected static string $resource = ProcesoVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
