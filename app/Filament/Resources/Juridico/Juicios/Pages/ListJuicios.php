<?php

namespace App\Filament\Resources\Juridico\Juicios\Pages;

use App\Filament\Resources\Juridico\Juicios\JuicioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJuicios extends ListRecords
{
    protected static string $resource = JuicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
