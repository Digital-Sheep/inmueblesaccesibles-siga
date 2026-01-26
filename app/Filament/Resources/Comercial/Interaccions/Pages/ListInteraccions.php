<?php

namespace App\Filament\Resources\Comercial\Interaccions\Pages;

use App\Filament\Resources\Comercial\Interaccions\InteraccionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListInteraccions extends ListRecords
{
    protected static string $resource = InteraccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva interacción')
                ->modalHeading('Nueva interacción')
                ->modalWidth('xl')
                ->createAnother(false)
                ->visible(
                    function() {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('interacciones_crear');
                    }
                ),
        ];
    }
}
