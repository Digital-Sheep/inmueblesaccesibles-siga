<?php

namespace App\Filament\Resources\Configuracion\CatSucursals\Pages;

use App\Filament\Resources\Configuracion\CatSucursals\CatSucursalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatSucursals extends ListRecords
{
    protected static string $resource = CatSucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
