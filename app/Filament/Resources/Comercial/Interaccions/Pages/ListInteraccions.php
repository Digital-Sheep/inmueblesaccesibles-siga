<?php

namespace App\Filament\Resources\Comercial\Interaccions\Pages;

use App\Filament\Resources\Comercial\Interaccions\InteraccionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInteraccions extends ListRecords
{
    protected static string $resource = InteraccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
