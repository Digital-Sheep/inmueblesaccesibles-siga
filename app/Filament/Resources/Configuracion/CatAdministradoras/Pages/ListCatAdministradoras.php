<?php

namespace App\Filament\Resources\Configuracion\CatAdministradoras\Pages;

use App\Filament\Resources\Configuracion\CatAdministradoras\CatAdministradoraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatAdministradoras extends ListRecords
{
    protected static string $resource = CatAdministradoraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
