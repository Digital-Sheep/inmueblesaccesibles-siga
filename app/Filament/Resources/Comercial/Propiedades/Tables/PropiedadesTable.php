<?php

namespace App\Filament\Resources\Comercial\Propiedades\Tables;

use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PropiedadesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Identificación Principal
                TextColumn::make('numero_credito')
                    ->label('Propiedad')
                    ->description(fn(Propiedad $record) => Str::limit($record->direccion_completa, 50))
                    ->searchable(['numero_credito', 'direccion_completa'])
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('ID copiado'),

                // 2. Precio
                TextColumn::make('precio_venta_sugerido')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),

                // 3. Ubicación
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->sortable()
                    ->searchable(),

                // 4. Semáforo comercial
                TextColumn::make('estatus_comercial')
                    ->badge()
                    ->label('Estatus Venta')
                    ->color(fn(string $state): string => match ($state) {
                        'DISPONIBLE' => 'success', // Verde
                        'APARTADA' => 'warning',   // Amarillo
                        'VENDIDA' => 'info',       // Azul
                        'BAJA', 'BORRADOR' => 'gray',
                        'EN_REVISION' => 'danger', // Rojo
                        default => 'gray',
                    }),

                // 5. Semáforo legal
                TextColumn::make('estatus_legal')
                    ->badge()
                    ->label('Jurídico')
                    ->icon(fn(string $state): string => match ($state) {
                        'R2_POSITIVO' => 'heroicon-o-check-circle',
                        'R1_NEGATIVO' => 'heroicon-o-x-circle',
                        'LITIGIO' => 'heroicon-o-scale',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'R2_POSITIVO', 'ADJUDICADA', 'ESCRITURADA' => 'success',
                        'R1_NEGATIVO' => 'danger',
                        'LITIGIO' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('estatus_comercial')
                    ->options([
                        'BORRADOR' => 'Borradores',
                        'DISPONIBLE' => 'Disponibles',
                        'APARTADA' => 'Apartadas',
                        'VENDIDA' => 'Vendidas',
                    ])
                    ->label('Filtrar por estatus')
                    ->native(false),

                SelectFilter::make('sucursal_id')
                    ->relationship('sucursal', 'nombre')
                    ->label('Sucursal')
                    ->native(false),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Datos de la propiedad')
                    ->modalWidth('4xl')
                    ->slideOver()
                    ->button()
                    ->label('Detalles')
                    ->visible(
                        function (Propiedad $record) {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->can('propiedades_editar');
                        }
                    ),

                Action::make('validar_publicacion')
                    ->label('Validar y Publicar')
                    ->icon('heroicon-o-check-badge')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Publicar propiedad?')
                    ->modalDescription('Al validar, esta propiedad pasará a DISPONIBLE y será visible para todos los asesores.')
                    ->visible(
                        function (Propiedad $record) {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return in_array($record->estatus_comercial, ['BORRADOR', 'EN_REVISION']) &&
                                   $user->can('propiedades_cambiar_estatus');
                        }
                    )

                    ->action(function (Propiedad $record) {
                        if ($record->precio_venta_sugerido <= 0) {
                            Notification::make()
                                ->danger()
                                ->title('Datos Incompletos')
                                ->body('No puedes publicar una propiedad sin precio de venta. Edítala primero.')
                                ->send();
                            return;
                        }

                        $record->update([
                            'estatus_comercial' => 'DISPONIBLE',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Propiedad Publicada')
                            ->body("La propiedad {$record->numero_credito} ya está disponible para venta.")
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Eliminar')
                    ->button()
                    ->modalHeading('¿Eliminar propiedad?')
                    ->modalDescription('Esta acción es irreversible. La propiedad será eliminada permanentemente.')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->visible(
                        function (Propiedad $record) {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->can('propiedades_eliminar') && $record->estatus_comercial === 'BORRADOR';
                        }
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
