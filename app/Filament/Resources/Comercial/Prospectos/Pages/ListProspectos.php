<?php

namespace App\Filament\Resources\Comercial\Prospectos\Pages;

use App\Filament\Resources\Comercial\Prospectos\ProspectoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProspectos extends ListRecords
{
    protected static string $resource = ProspectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
