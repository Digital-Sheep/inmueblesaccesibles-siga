<?php

namespace App\Filament\Resources\Comercial\Carteras\Pages;

use App\Filament\Resources\Comercial\Carteras\CarteraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarteras extends ListRecords
{
    protected static string $resource = CarteraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
