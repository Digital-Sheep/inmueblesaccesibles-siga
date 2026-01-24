<?php

namespace App\Filament\Resources\Configuracion\Roles\Pages;

use App\Filament\Resources\Configuracion\Roles\RolResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewRol extends ViewRecord
{
    protected static string $resource = RolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Editar Rol')
                ->icon('heroicon-o-pencil')
                ->modalHeading(fn($record) => "Editar Rol: {$record->name}")
                ->modalWidth('5xl')
                ->visible(function ($record) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return $user->can('roles_editar')
                        && !in_array($record->name, ['Super_Admin', 'DGE']);
                }),
        ];
    }
}
