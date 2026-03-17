<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Pages;

use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use App\Filament\Resources\Juridico\SeguimientoNotarias\Schemas\SeguimientoNotariaForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;

class CreateSeguimientoNotaria extends CreateRecord
{
    use HasWizard;

    protected static string $resource = SeguimientoNotariaResource::class;

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Información General')
                ->icon('heroicon-o-information-circle')
                ->description('Identificación y clasificación')
                ->schema(SeguimientoNotariaForm::camposInformacionGeneral()),

            Step::make('Cesión y Seguimiento')
                ->icon('heroicon-o-clipboard-document-check')
                ->description('Cesión de derechos y estado actual')
                ->schema(SeguimientoNotariaForm::camposCesionYSeguimiento()),
        ];
    }
}
