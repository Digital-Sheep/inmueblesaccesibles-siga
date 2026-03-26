<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\Pages;

use App\Filament\Resources\Juridico\SeguimientoDictamenes\Schemas\SeguimientoDictamenForm;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\SeguimientoDictamenURRJResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;

class CreateSeguimientoDictamenURRJ extends CreateRecord
{
    use HasWizard;
    protected static string $resource = SeguimientoDictamenURRJResource::class;
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
            Step::make('Información General')->icon('heroicon-o-information-circle')
                ->schema(SeguimientoDictamenForm::camposInformacionGeneral()),
            Step::make('Dictamen')->icon('heroicon-o-document-magnifying-glass')
                ->schema(SeguimientoDictamenForm::camposDictamen()),
            Step::make('Valores')->icon('heroicon-o-currency-dollar')
                ->schema(SeguimientoDictamenForm::camposValores()),
            Step::make('Seguimiento')->icon('heroicon-o-document-text')
                ->schema(SeguimientoDictamenForm::camposSeguimiento()),
        ];
    }
}
