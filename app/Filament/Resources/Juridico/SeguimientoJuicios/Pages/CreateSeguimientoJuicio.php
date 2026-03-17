<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Pages;

use App\Filament\Resources\Juridico\SeguimientoJuicios\SeguimientoJuicioResource;
use App\Filament\Resources\Juridico\SeguimientoJuicios\Schemas\SeguimientoJuicioForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;

class CreateSeguimientoJuicio extends CreateRecord
{
    use HasWizard;

    protected static string $resource = SeguimientoJuicioResource::class;

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
                ->description('Identificación y clasificación del juicio')
                ->schema(SeguimientoJuicioForm::camposInformacionGeneral()),

            Step::make('Datos del Juicio')
                ->icon('heroicon-o-scale')
                ->description('Partes, expediente y cesión de derechos')
                ->schema(SeguimientoJuicioForm::camposDatosJuicio()),

            Step::make('Seguimiento')
                ->icon('heroicon-o-document-text')
                ->description('Estado actual y estrategia jurídica')
                ->schema(SeguimientoJuicioForm::camposSeguimiento()),
        ];
    }
}
