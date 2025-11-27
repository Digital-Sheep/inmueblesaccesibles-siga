<?php

namespace App\Filament\Resources\Comercial\Prospectos\Pages;

use App\Filament\Resources\Comercial\Prospectos\ProspectoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProspecto extends ViewRecord
{
    protected static string $resource = ProspectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
