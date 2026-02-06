<?php

namespace App\Filament\Resources\Configuracion\TabuladorCostos\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TabuladorCostosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tamano_propiedad')
                    ->label('Categoría')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'CHICA' => 'Chica (0-80 m²)',
                        'MEDIANA' => 'Mediana (81-150 m²)',
                        'GRANDE' => 'Grande (151-250 m²)',
                        'MUY_GRANDE' => 'Muy Grande (251+ m²)',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('costo_remodelacion')
                    ->label('Remodelación')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('costo_luz')
                    ->label('Luz')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('costo_agua')
                    ->label('Agua')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('costo_predial')
                    ->label('Predial')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('costo_gastos_juridicos')
                    ->label('Gastos Jurídicos')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        return $record->costo_remodelacion +
                            $record->costo_luz +
                            $record->costo_agua +
                            $record->costo_predial +
                            $record->costo_gastos_juridicos;
                    }),

                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tamano_propiedad')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editar Costos')
                    ->modalWidth('3xl')
                    ->button()
                    ->closeModalByClickingAway(false),
            ]);
    }
}
