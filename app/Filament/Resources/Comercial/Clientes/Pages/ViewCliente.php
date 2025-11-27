<?php

namespace App\Filament\Resources\Comercial\Clientes\Pages;

use App\Filament\Resources\Comercial\Clientes\ClienteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
