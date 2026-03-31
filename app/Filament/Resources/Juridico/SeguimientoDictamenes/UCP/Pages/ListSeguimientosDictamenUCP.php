<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\SeguimientoDictamenUCPResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

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
        return [CreateAction::make()
            ->label('Nuevo dictamen')
            ->visible(
                function ($record) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return $record->activo && $user->can('seguimientodictamenes_crear');
                }
            )];
    }
}
