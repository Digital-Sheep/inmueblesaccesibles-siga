<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\SeguimientoDictamenUCPResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeguimientosDictamenUCP extends ListRecords
{
    protected static string $resource = SeguimientoDictamenUCPResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            JuridicoCluster::getUrl() => 'Juridico',
            SeguimientoDictamenUCPResource::getUrl('index') => 'Seguimiento de Dictámenes UCP',
            // "Dictamen #{$this->record->id}",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
