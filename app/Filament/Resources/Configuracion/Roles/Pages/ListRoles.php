<?php

namespace App\Filament\Resources\Configuracion\Roles\Pages;

use App\Filament\Resources\Configuracion\Roles\RolResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class ListRoles extends ListRecords
{
    protected static string $resource = RolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Nuevo Rol')
                ->color('primary')
                ->modalSubmitActionLabel('Guardar')
                ->schema(fn($form) => RolResource::form($form->schema([]))->getComponents())
                ->action(function (array $data) {
                    // Crear rol
                    $role = Role::create(['name' => $data['name']]);

                    // Combinar permisos
                    $todosLosPermisos = [];
                    if (isset($data['permissions_data'])) {
                        foreach ($data['permissions_data'] as $categoria => $permisos) {
                            if (is_array($permisos)) {
                                $todosLosPermisos = array_merge($todosLosPermisos, $permisos);
                            }
                        }
                    }

                    // Sincronizar
                    $role->syncPermissions($todosLosPermisos);

                    Notification::make()
                        ->success()
                        ->title('Rol creado')
                        ->body("El rol '{$role->name}' ha sido creado con " . count($todosLosPermisos) . " permisos.")
                        ->send();
                })
                ->modalHeading('Crear Nuevo Rol')
                ->modalWidth('5xl')
                ->visible(
                    function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('roles_crear');
                    }
                ),
        ];
    }
}
