<?php

namespace App\Filament\Resources\Juridico\ExpedienteJuridicos\Pages;

use App\Filament\Resources\Juridico\ExpedienteJuridicos\ExpedienteJuridicoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExpedienteJuridico extends EditRecord
{
    protected static string $resource = ExpedienteJuridicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
