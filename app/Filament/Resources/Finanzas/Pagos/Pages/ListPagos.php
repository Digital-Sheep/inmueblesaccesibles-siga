<?php

namespace App\Filament\Resources\Finanzas\Pagos\Pages;

use App\Filament\Resources\Finanzas\Pagos\PagoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPagos extends ListRecords
{
    protected static string $resource = PagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
