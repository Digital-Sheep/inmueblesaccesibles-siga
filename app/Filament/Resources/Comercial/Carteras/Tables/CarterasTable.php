<?php

namespace App\Filament\Resources\Comercial\Carteras\Tables;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\Cartera;
use App\Models\User;
use App\Services\ImportadorCarteras;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CarterasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('administradora.nombre')
                    ->label('Administradora')
                    ->sortable(),

                TextColumn::make('fecha_recepcion')
                    ->label('Fecha de corte')
                    ->date('d/M/Y')
                    ->sortable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PUBLICADA' => 'success',
                        'PROCESADA' => 'info',
                        'BORRADOR' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Cargado')
                    ->date('d/M/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('administradora_id')
                    ->relationship('administradora', 'nombre')
                    ->label('Por administradora'),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Datos de la cartera')
                    ->modalWidth('2xl')
                    ->slideOver()
                    ->button()
                    ->label('Detalles'),

                Action::make('procesar_importacion')
                    ->label('Procesar archivo')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Procesar e importar propiedades?')
                    ->modalDescription('El sistema leerá el archivo CSV y creará las propiedades en la base de datos vinculadas a esta cartera y sucursal.')
                    ->visible(fn(Cartera $record) => $record->estatus === 'BORRADOR' && $record->archivo_path)
                    ->action(function (Cartera $record) {

                        try {
                            $importador = new ImportadorCarteras();
                            $resultado = $importador->importar($record);

                            Notification::make()
                                ->success()
                                ->title('Procesamiento Exitoso')
                                ->body("✅ Importadas: {$resultado['procesados']}\n⏭️ Duplicadas: {$resultado['duplicados']}")
                                ->send();

                            $supervisores = User::where('sucursal_id', $record->sucursal_id)->role(['Direccion_Legal', 'Direccion_Comercial'])->get();

                            Notification::make()
                                ->title('📢 Nueva cartera por validar')
                                ->body("Se han cargado  {$resultado['procesados']} propiedades nuevas para tu sucursal en la cartera {$record->nombre}.\n\nPor favor, revisa los borradores y valídalos para venta.")
                                ->warning()
                                ->actions([
                                    Action::make('revisar')
                                        ->label('Ir a borradores')
                                        ->button()
                                        ->url(
                                            PropiedadResource::getUrl('index', [
                                                'tableFilters' => [
                                                    'estatus_comercial' => [
                                                        'value' => 'BORRADOR'
                                                    ]
                                                ]
                                            ]),
                                            shouldOpenInNewTab: true
                                        ),
                                ])
                                ->sendToDatabase($supervisores);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error en la Importación')
                                ->body($e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),

                DeleteAction::make()
                    ->label('Eliminar')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar cartera?')
                    ->modalDescription('Esta acción es irreversible.')
                    ->action(function (Cartera $record) {
                        $record->delete();
                    })
                    ->visible(
                        function (Cartera $record) {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->can('carteras_eliminar') && $record->estatus === 'BORRADOR';
                        }
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
