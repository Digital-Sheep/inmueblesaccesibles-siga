<?php

namespace App\Filament\Resources\Configuracion\EtapasProcesales\Pages;

use App\Filament\Resources\Configuracion\EtapasProcesales\EtapaProcesalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEtapasProcesales extends ListRecords
{
    protected static string $resource = EtapaProcesalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Nueva etapa procesal')
                ->modalWidth('6xl')
                ->createAnother(false)
                ->closeModalByClickingAway(false)
                ->mutateDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();
                    $data['updated_by'] = auth()->id();
                    return $data;
                }),
        ];
    }

    // public function getSubheading(): ?string
    // {
    //     return '💡 Catálogo único usado tanto en el Cotizador como en el Módulo Jurídico.';
    // }
}
