<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Pages;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\ProcesoVentas\ProcesoVentaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProcesoVenta extends ViewRecord
{
    protected static string $resource = ProcesoVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ComercialCluster::getUrl() => 'Comercial',
            ProcesoVentaResource::getUrl('index') => 'Procesos de Venta',
            "#{$this->record->id}",
        ];
    }
}
