<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Pages;

use App\Filament\Resources\Juridico\SeguimientoJuicios\SeguimientoJuicioResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSeguimientoJuicio extends EditRecord
{
    protected static string $resource = SeguimientoJuicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
