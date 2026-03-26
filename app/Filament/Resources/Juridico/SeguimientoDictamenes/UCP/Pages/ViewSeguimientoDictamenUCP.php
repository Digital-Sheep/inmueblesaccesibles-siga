<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\ActuacionesDictamenRelationManager;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\SeguimientoDictamenUCPResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeguimientoDictamenUCP extends ViewRecord
{
    protected static string $resource = SeguimientoDictamenUCPResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            JuridicoCluster::getUrl() => 'Juridico',
            SeguimientoDictamenUCPResource::getUrl('index') => 'Seguimiento de Dictámenes UCP',
            "Dictamen #{$this->record->id}",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
    public function getRelationManagers(): array
    {
        return [ActuacionesDictamenRelationManager::class];
    }
}
