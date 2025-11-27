<?php

namespace App\Filament\Resources\Finanzas\Pagos\Pages;

use App\Filament\Resources\Finanzas\Pagos\PagoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPago extends ViewRecord
{
    protected static string $resource = PagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
