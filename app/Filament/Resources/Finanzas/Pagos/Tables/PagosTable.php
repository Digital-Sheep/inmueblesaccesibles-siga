<?php

namespace App\Filament\Resources\Finanzas\Pagos\Tables;

use App\Models\Pago;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('monto')
                    ->money('MXN')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('metodo_pago')
                    ->label('Método')
                    ->sortable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'VALIDADO' => 'success',
                        'RECHAZADO' => 'danger',
                        'PENDIENTE' => 'warning',
                        default => 'gray',
                    }),

                // Mostrar quién pagó (Polimórfico)
                TextColumn::make('pagable_type')
                    ->label('Cliente / Prospecto')
                    ->formatStateUsing(function (Pago $record) {
                        if (!$record->pagable) return 'S/N';
                        // Intentamos obtener el nombre, sea cliente o prospecto
                        return $record->pagable->nombre_completo
                            ?? ($record->pagable->nombres . ' ' . $record->pagable->apellido_paterno);
                    })
                    ->description(fn(Pago $record) => 'Ref: Venta #' . $record->proceso_venta_id),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/M/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->options([
                        'PENDIENTE' => 'Pendientes de Validar',
                        'VALIDADO' => 'Ya Validados',
                    ])
                    ->default('PENDIENTE'), // Por defecto mostramos lo que urge
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                // ACCIÓN RÁPIDA: VALIDAR
                Action::make('validar')
                    ->label('Validar Pago')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Pago $record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $record->estatus === 'PENDIENTE' && $user->hasRole(['Super_Admin', 'Administracion']);
                    })
                    ->action(function (Pago $record) {
                        $record->update([
                            'estatus' => 'VALIDADO',
                            'validado_por_id' => Auth::id(),
                            'fecha_validacion' => now(),
                        ]);

                        // AQUÍ DISPARAREMOS LA NOTIFICACIÓN AL ASESOR MÁS ADELANTE
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
