<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Pages;

use App\Filament\Resources\Comercial\ProcesoVentas\ProcesoVentaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProcesoVenta extends CreateRecord
{
    protected static string $resource = ProcesoVentaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si vendedor_id viene vacío (campo disabled no se envía),
        // lo asignamos al usuario autenticado
        if (empty($data['vendedor_id'])) {
            $data['vendedor_id'] = Auth::id();
        }

        return $data;
    }
}
