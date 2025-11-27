<?php

namespace App\Filament\Resources\Configuracion\CatAdministradoras\Pages;

use App\Filament\Resources\Configuracion\CatAdministradoras\CatAdministradoraResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCatAdministradora extends EditRecord
{
    protected static string $resource = CatAdministradoraResource::class;

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
