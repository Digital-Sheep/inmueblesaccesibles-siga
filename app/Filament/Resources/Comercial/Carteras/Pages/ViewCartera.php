<?php

namespace App\Filament\Resources\Comercial\Carteras\Pages;

use App\Filament\Resources\Comercial\Carteras\CarteraResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCartera extends ViewRecord
{
    protected static string $resource = CarteraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
