<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Pages;

use App\Filament\Resources\Juridico\SeguimientoJuicios\ActuacionesJuicioRelationManager;
use App\Filament\Resources\Juridico\SeguimientoJuicios\SeguimientoJuicioResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeguimientoJuicio extends ViewRecord
{
    protected static string $resource = SeguimientoJuicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * En Filament v4 los RelationManagers deben declararse
     * también en la ViewPage para que se rendericen aquí.
     */
    public function getRelationManagers(): array
    {
        return [
            ActuacionesJuicioRelationManager::class,
        ];
    }
}
