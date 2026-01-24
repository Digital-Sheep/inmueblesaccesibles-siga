<?php

namespace App\Filament\Resources\Configuracion\Roles\Tables;

use App\Filament\Resources\Configuracion\Roles\RolResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre del Rol')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('primary')
                    ->copyable()
                    ->copyMessage('Rol copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0 => 'gray',
                        $state <= 5 => 'info',
                        $state <= 20 => 'success',
                        default => 'warning',
                    })
                    ->icon('heroicon-o-user-group'),

                TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0 => 'danger',
                        $state <= 20 => 'warning',
                        $state <= 50 => 'info',
                        default => 'success',
                    })
                    ->icon('heroicon-o-key'),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/M/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Última Modificación')
                    ->dateTime('d/M/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->since(),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Filter::make('sin_usuarios')
                    ->label('Sin usuarios asignados')
                    ->query(fn(Builder $query) => $query->has('users', '=', 0))
                    ->toggle(),

                Filter::make('sin_permisos')
                    ->label('Sin permisos asignados')
                    ->query(fn(Builder $query) => $query->has('permissions', '=', 0))
                    ->toggle(),
            ])
            // ->recordAction(ViewAction::class)
            ->recordActions([
                ActionGroup::make([
                    // EDITAR ROL (Modal)
                    Action::make('edit')
                        ->label('Editar')
                        ->icon('heroicon-o-pencil')
                        ->modalSubmitActionLabel('Guardar')
                        ->fillForm(fn($record) => [
                            'name' => $record->name,
                        ])
                        ->schema(fn($form) => RolResource::form($form->schema([]))->getComponents())
                        ->action(function (array $data, $record) {
                            $record->update(['name' => $data['name']]);

                            $todosLosPermisos = [];

                            if (isset($data['permissions_data'])) {
                                foreach ($data['permissions_data'] as $categoria => $permisos) {
                                    if (is_array($permisos)) {
                                        $todosLosPermisos = array_merge($todosLosPermisos, $permisos);
                                    }
                                }
                            }

                            $record->syncPermissions($todosLosPermisos);

                            Notification::make()
                                ->success()
                                ->title('Rol actualizado')
                                ->body("El rol '{$record->name}' ahora tiene " . count($todosLosPermisos) . " permisos.")
                                ->send();
                        })
                        ->modalHeading(fn($record) => "Editar Rol: {$record->name}")
                        ->modalWidth('5xl')
                        ->visible(
                            function ($record) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $user->can('roles_editar');
                            }
                        ),

                    Action::make('ver_detalles')
                        ->label('Ver Detalles')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn($record) => RolResource::getUrl('view', ['record' => $record])),

                    Action::make('delete')
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => "¿Eliminar rol '{$record->name}'?")
                        ->modalDescription('Esta acción no se puede deshacer. Los usuarios perderán este rol.')
                        ->modalSubmitActionLabel('Eliminar')
                        ->action(function ($record) {
                            $record->delete();

                            Notification::make()
                                ->success()
                                ->title('Rol eliminado')
                                ->body("El rol '{$record->name}' ha sido eliminado.")
                                ->send();
                        })
                        ->before(function ($record, Action $action) {
                            // Prevenir eliminación de roles del sistema
                            if (in_array($record->name, ['Super_Admin', 'DGE'])) {
                                Notification::make()
                                    ->danger()
                                    ->title('Rol protegido')
                                    ->body('Los roles del sistema no pueden ser eliminados.')
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }

                            // Advertir si tiene usuarios
                            if ($record->users()->count() > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('Rol en uso')
                                    ->body("Este rol tiene {$record->users()->count()} usuario(s) asignados. Al eliminarlo, perderán este rol.")
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->visible(
                            function ($record) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                if (in_array($record->name, ['Super_Admin', 'DGE'])) {
                                    return false;
                                }

                                return $user->can('roles_eliminar');
                            }
                        ),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->tooltip('Acciones'),
            ])
            ->emptyStateHeading('No hay roles registrados')
            ->emptyStateDescription('Crea el primer rol para comenzar a gestionar permisos.')
            ->emptyStateIcon('heroicon-o-shield-exclamation')
            ->striped();
    }
}
