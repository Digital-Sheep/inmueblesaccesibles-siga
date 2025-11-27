<?php

namespace App\Filament\Resources\Comercial\Prospectos\Pages;

use App\Filament\Resources\Comercial\Prospectos\ProspectoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProspecto extends EditRecord
{
    protected static string $resource = ProspectoResource::class;

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
