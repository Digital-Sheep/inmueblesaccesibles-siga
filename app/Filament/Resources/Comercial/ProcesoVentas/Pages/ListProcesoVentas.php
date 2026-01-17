<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Pages;

use App\Filament\Resources\Comercial\ProcesoVentas\ProcesoVentaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProcesoVentas extends ListRecords
{
    protected static string $resource = ProcesoVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo proceso de venta')
                ->modalHeading('Registrar nuevo proceso de venta')
                ->modalWidth('lg')
                ->createAnother(false),
        ];
    }
}
