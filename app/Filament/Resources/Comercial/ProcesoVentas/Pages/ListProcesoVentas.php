<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Pages;

use App\Filament\Resources\Comercial\ProcesoVentas\ProcesoVentaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProcesoVentas extends ListRecords
{
    protected static string $resource = ProcesoVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo proceso de venta')
                ->modalHeading('Registrar nuevo proceso de venta')
                ->modalWidth('lg')
                ->createAnother(false)
                ->visible(
                    function (): bool {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('ventas_crear');
                    }
                ),
        ];
    }
}
