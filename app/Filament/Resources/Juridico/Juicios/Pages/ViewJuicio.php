<?php

namespace App\Filament\Resources\Juridico\Juicios\Pages;

use App\Filament\Resources\Juridico\Juicios\JuicioResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJuicio extends ViewRecord
{
    protected static string $resource = JuicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
