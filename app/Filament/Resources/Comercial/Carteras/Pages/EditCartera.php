<?php

namespace App\Filament\Resources\Comercial\Carteras\Pages;

use App\Filament\Resources\Comercial\Carteras\CarteraResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCartera extends EditRecord
{
    protected static string $resource = CarteraResource::class;

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
