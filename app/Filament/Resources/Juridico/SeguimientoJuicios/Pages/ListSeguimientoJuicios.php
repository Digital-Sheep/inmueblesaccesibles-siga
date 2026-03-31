<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Pages;

use App\Filament\Resources\Juridico\SeguimientoJuicios\SeguimientoJuicioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSeguimientoJuicios extends ListRecords
{
    protected static string $resource = SeguimientoJuicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo juicio')
                ->visible(
                    function ($record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('seguimientojuicios_crear');
                    }
                ),
        ];
    }
}
