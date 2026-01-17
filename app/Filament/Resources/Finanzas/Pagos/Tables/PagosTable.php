<?php

namespace App\Filament\Resources\Finanzas\Pagos\Tables;

use App\Models\Archivo;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\ProcesoCompra;
use App\Models\ProcesoVenta;
use App\Models\Prospecto;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Illuminate\Support\Facades\Auth;

class PagosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('concepto')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'APARTADO' => 'warning',
                        'ENGANCHE' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('monto')
                    ->money('MXN')
                    ->weight('bold'),

                TextColumn::make('metodo_pago')
                    ->label('Método'),

                // Semáforo de Estatus
                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDIENTE' => 'danger',
                        'VALIDADO' => 'success',
                        'RECHAZADO' => 'gray',
                    }),

                TextColumn::make('comprobante_url')
                    ->label('Comprobante')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            return "<a href='" . asset("storage/{$state}") . "' target='_blank'>Ver comprobante</a>";
                        }

                        return 'No disponible';
                    })
                    ->html(),

            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->options([
                        'PENDIENTE' => 'Pendientes de Validar',
                        'VALIDADO' => 'Histórico Validado',
                    ])
                    ->default('PENDIENTE'),
            ])
            ->recordActions([
                Action::make('validar')
                    ->label('Validar ingreso')
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(Pago $record) => $record->estatus === 'PENDIENTE')
                    ->action(function (Pago $record) {
                        $record->update([
                            'estatus' => 'VALIDADO',
                            'validado_por_id' => Auth::id(),
                            'fecha_validacion' => now(),
                        ]);

                        if ($record->concepto === 'APARTADO') {
                            $proceso = $record->procesoVenta;
                            $interesado = $proceso->interesado;

                            if ($interesado instanceof Prospecto) {

                                // Crear el Cliente
                                $nuevoCliente = Cliente::create([
                                    'nombres' => $interesado->nombre_completo,
                                    'apellido_paterno' => '.',
                                    'email' => $interesado->email,
                                    'celular' => $interesado->celular,
                                    'sucursal_id' => $interesado->sucursal_id,
                                    'usuario_responsable_id' => $interesado->usuario_responsable_id,
                                    'origen' => 'CONVERSION_PROSPECTO',
                                ]);

                                // Mudar el proceso de venta al nuevo cliente
                                $proceso->update([
                                    'interesado_type' => Cliente::class,
                                    'interesado_id' => $nuevoCliente->id,
                                    'estatus' => 'APARTADO_VALIDADO',
                                ]);

                                Archivo::where('entidad_type', Prospecto::class)
                                    ->where('entidad_id', $interesado->id)
                                    ->update([
                                        'entidad_type' => Cliente::class,
                                        'entidad_id'   => $nuevoCliente->id,
                                    ]);

                                // Cerrar el prospecto
                                $interesado->update([
                                    'estatus' => 'CLIENTE',
                                    'convertido_a_cliente_id' => $nuevoCliente->id,
                                ]);

                                Notification::make()
                                    ->success()
                                    ->title('¡Nuevo Cliente!')
                                    ->body("El prospecto ha sido convertido a Cliente y la venta avanza a Dictaminación.")
                                    ->send();
                            } else {
                                $proceso->update(['estatus' => 'APARTADO_VALIDADO']);
                            }
                        }

                        if (in_array($record->concepto, ['ENGANCHE', 'LIQUIDACION'])) {

                            $procesoVenta = $record->procesoVenta;

                            $existeCompra = ProcesoCompra::where('proceso_venta_id', $procesoVenta->id)->exists();

                            if (!$existeCompra) {
                                $dictamen = $procesoVenta->dictamenes()->where('estatus', 'TERMINADO')->latest()->first();

                                ProcesoCompra::create([
                                    'proceso_venta_id' => $procesoVenta->id,
                                    'propiedad_id' => $procesoVenta->propiedad_id,
                                    'dictamen_id' => $dictamen?->id,
                                    'tipo_compra' => $dictamen?->nomenclatura_generada ?? 'R2',
                                    'estatus' => 'INICIADO',
                                    'responsable_id' => Auth::id(),
                                    'created_by' => Auth::id(),
                                ]);

                                // Actualizar estatus de la venta para reflejar que ya estamos comprando
                                $procesoVenta->update(['estatus' => 'EN_PROCESO_COMPRA']);

                                Notification::make()
                                    ->success()
                                    ->title('Proceso de compra iniciado')
                                    ->body('Se ha abierto el expediente administrativo para la adquisición de la propiedad.')
                                    ->send();
                            }
                        }
                    }),

                Action::make('rechazar')
                    ->label('Rechazar')
                    ->button()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(Pago $record) => $record->estatus === 'PENDIENTE')
                    ->schema([
                        Textarea::make('motivo')->required()
                    ])
                    ->action(function (Pago $record, array $data) {
                        $record->update(['estatus' => 'RECHAZADO']);

                        Notification::make()
                            ->title('Pago rechazado')
                            ->body("El pago de $ {$record->monto} ha sido rechazado.\n\nMotivo: {$data['motivo']}")
                            ->danger()
                            ->actions([
                                Action::make('ver')
                                    ->button()
                                    ->url(route('filament.admin.resources.comercial.proceso-ventas.view', $record->proceso_venta_id), shouldOpenInNewTab: true),
                            ])
                            ->sendToDatabase($record->procesoVenta->vendedor);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
