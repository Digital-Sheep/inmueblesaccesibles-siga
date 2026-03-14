<?php
namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Pages;
use App\Filament\Resources\Juridico\SeguimientoNotarias\ActuacionesNotariaRelationManager;
use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeguimientoNotaria extends ViewRecord {
    protected static string $resource = SeguimientoNotariaResource::class;

    protected function getHeaderActions(): array { return [EditAction::make()]; }

    public function getRelationManagers(): array
    {
        return [
            ActuacionesNotariaRelationManager::class,
        ];
    }
}
