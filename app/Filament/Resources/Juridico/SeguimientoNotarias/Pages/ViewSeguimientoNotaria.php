<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoNotarias\ActuacionesNotariaRelationManager;
use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

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
        return [EditAction::make()
            ->visible(function () {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                return $user->can('seguimientonotarias_editar');
             })];
    }

    public function getRelationManagers(): array
    {
        return [
            ActuacionesNotariaRelationManager::class,
        ];
    }
}
