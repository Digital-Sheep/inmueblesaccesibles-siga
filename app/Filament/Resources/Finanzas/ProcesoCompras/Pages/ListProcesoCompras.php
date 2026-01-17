<?php

namespace App\Filament\Resources\Finanzas\ProcesoCompras\Pages;

use App\Filament\Resources\Finanzas\ProcesoCompras\ProcesoCompraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProcesoCompras extends ListRecords
{
    protected static string $resource = ProcesoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
