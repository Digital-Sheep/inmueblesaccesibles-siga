<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\SeguimientoDictamenUCPResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSeguimientoDictamenUCP extends EditRecord
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
        return [ViewAction::make(), DeleteAction::make()];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
