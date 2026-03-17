<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoNotarias\ActuacionesNotariaRelationManager;
use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeguimientoNotaria extends ViewRecord
{
    protected static string $resource = SeguimientoNotariaResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            JuridicoCluster::getUrl() => 'Juridico',
            SeguimientoNotariaResource::getUrl('index') => 'Seguimiento de Notarias',
            "Seguimiento #{$this->record->id}",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }

    public function getRelationManagers(): array
    {
        return [
            ActuacionesNotariaRelationManager::class,
        ];
    }
}
