<?php

namespace App\Filament\Resources\Comercial\Propiedades\Pages;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListPropiedades extends ListRecords
{
    protected static string $resource = PropiedadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Agregar propiedad')
                ->visible(
                    function(): bool {
                        /** @var \App\Models\User  $user */
                        $user = Auth::user();

                        return $user->can('propiedades_crear');
                    }
                ),
        ];
    }
}
