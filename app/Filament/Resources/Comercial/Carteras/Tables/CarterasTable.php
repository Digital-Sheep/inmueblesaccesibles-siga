<?php

namespace App\Filament\Resources\Comercial\Carteras\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
                    ->label('Fecha de Corte')
                    ->date('d/M/Y')
                    ->sortable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PUBLICADA' => 'success',
                        'VALIDADA' => 'info',
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
                    ->label('Por Administradora'),
            ])
            ->actions([
                // Aquí agregaremos la acción de "Procesar y Validar" más adelante
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
