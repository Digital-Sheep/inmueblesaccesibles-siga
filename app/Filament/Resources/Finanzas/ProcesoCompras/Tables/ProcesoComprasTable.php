<?php

namespace App\Filament\Resources\Finanzas\ProcesoCompras\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProcesoComprasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('propiedad.numero_credito')->label('Propiedad')->searchable(),
                TextColumn::make('tipo_compra')->badge()->color('info'),
                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'INICIADO' => 'gray',
                        'PAGADO_PROVEEDOR' => 'warning',
                        'FIRMADO_EXITOSO' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('precio_compra_negociado')->money('MXN')->label('Costo'),
            ])
            ->actions([
                EditAction::make(),
                // Acción para marcar como PAGADO AL BANCO
                Action::make('marcar_pagado')
                    ->label('Pago Realizado')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['estatus' => 'PAGADO_PROVEEDOR', 'fecha_pago_proveedor' => now()])),
            ]);
    }
}
