<?php

namespace App\Filament\Resources\Juridico\Dictamens\Pages;

use App\Filament\Resources\Juridico\Dictamens\DictamenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDictamens extends ListRecords
{
    protected static string $resource = DictamenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
