<?php

namespace App\Filament\Resources\Comercial\Interaccions\Pages;

use App\Filament\Resources\Comercial\Interaccions\InteraccionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInteraccion extends ViewRecord
{
    protected static string $resource = InteraccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
