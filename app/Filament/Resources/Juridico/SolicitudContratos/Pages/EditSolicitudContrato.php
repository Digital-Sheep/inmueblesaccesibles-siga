<?php

namespace App\Filament\Resources\Juridico\SolicitudContratos\Pages;

use App\Filament\Resources\Juridico\SolicitudContratos\SolicitudContratoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudContrato extends EditRecord
{
    protected static string $resource = SolicitudContratoResource::class;

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
