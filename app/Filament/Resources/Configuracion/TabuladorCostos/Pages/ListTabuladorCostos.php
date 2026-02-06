<?php

namespace App\Filament\Resources\Configuracion\TabuladorCostos\Pages;

use App\Filament\Resources\Configuracion\TabuladorCostos\TabuladorCostosResource;
use Filament\Resources\Pages\ListRecords;

class ListTabuladorCostos extends ListRecords
{
    protected static string $resource = TabuladorCostosResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    // public function getSubheading(): ?string
    // {
    //     return 'Estos costos se usan automáticamente en el cotizador según el tamaño de la propiedad.';
    // }
}
