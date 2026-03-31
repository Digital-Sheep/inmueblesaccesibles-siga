<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\ActuacionesDictamenRelationManager;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\SeguimientoDictamenURRJResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

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
        return [EditAction::make()
            ->visible(
                function ($record) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return $record->activo && $user->can('seguimientodictamenes_editar');
                }
            )];
    }

    public function getRelationManagers(): array
    {
        return [ActuacionesDictamenRelationManager::class];
    }
}
