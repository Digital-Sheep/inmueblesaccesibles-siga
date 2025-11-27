<?php

namespace App\Filament\Resources\Comercial\Propiedades\Pages;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropiedades extends ListRecords
{
    protected static string $resource = PropiedadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
