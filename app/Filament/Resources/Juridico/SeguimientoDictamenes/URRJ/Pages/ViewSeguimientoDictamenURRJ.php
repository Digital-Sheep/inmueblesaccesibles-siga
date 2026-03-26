<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\ActuacionesDictamenRelationManager;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\SeguimientoDictamenURRJResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeguimientoDictamenURRJ extends ViewRecord
{
    protected static string $resource = SeguimientoDictamenURRJResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            JuridicoCluster::getUrl() => 'Juridico',
            SeguimientoDictamenURRJResource::getUrl('index') => 'Seguimiento de Dictámenes URRJ',
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
