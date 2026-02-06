<?php

namespace App\Filament\Resources\Comercial\Propiedades\Pages;

use App\Filament\Actions\CalcularCotizacionAction;
use App\Filament\Actions\ValidarYPublicarPropiedadAction;
use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPropiedad extends ViewRecord
{
    protected static string $resource = PropiedadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. EDITAR
            EditAction::make()
                ->label('Editar Propiedad')
                ->icon('heroicon-o-pencil')
                ->color('gray')
                ->visible(
                    function() {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('propiedades_editar');
                    }
                ),

            // 2. CALCULAR PRECIO
            CalcularCotizacionAction::make(),

            // 3. VALIDAR Y PUBLICAR
            ValidarYPublicarPropiedadAction::make(),
        ];
    }
}
