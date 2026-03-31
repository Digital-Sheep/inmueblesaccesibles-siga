<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\SeguimientoDictamenURRJResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSeguimientosDictamenURRJ extends ListRecords
{
    protected static string $resource = SeguimientoDictamenURRJResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            JuridicoCluster::getUrl() => 'Juridico',
            SeguimientoDictamenURRJResource::getUrl('index') => 'Seguimiento de Dictámenes URRJ',
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
