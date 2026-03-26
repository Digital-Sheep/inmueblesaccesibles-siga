<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages;

use App\Filament\Clusters\Juridico\JuridicoCluster;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\SeguimientoDictamenURRJResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSeguimientoDictamenURRJ extends EditRecord
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
        return [ViewAction::make(), DeleteAction::make()];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
