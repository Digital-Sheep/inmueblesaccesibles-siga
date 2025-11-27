<?php

namespace App\Filament\Resources\Juridico\Dictamens\Pages;

use App\Filament\Resources\Juridico\Dictamens\DictamenResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDictamen extends EditRecord
{
    protected static string $resource = DictamenResource::class;

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
