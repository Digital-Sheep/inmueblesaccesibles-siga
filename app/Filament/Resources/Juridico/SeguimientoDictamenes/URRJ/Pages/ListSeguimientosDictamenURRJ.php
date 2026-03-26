<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages;

use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\SeguimientoDictamenURRJResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeguimientosDictamenURRJ extends ListRecords
{
    protected static string $resource = SeguimientoDictamenURRJResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
