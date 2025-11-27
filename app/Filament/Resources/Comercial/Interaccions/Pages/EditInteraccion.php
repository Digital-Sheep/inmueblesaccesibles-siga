<?php

namespace App\Filament\Resources\Comercial\Interaccions\Pages;

use App\Filament\Resources\Comercial\Interaccions\InteraccionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInteraccion extends EditRecord
{
    protected static string $resource = InteraccionResource::class;

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
