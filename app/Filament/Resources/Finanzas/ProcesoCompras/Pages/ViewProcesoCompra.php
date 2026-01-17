<?php

namespace App\Filament\Resources\Finanzas\ProcesoCompras\Pages;

use App\Filament\Resources\Finanzas\ProcesoCompras\ProcesoCompraResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProcesoCompra extends ViewRecord
{
    protected static string $resource = ProcesoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
