<?php

namespace App\Filament\Resources\Comercial\Prospectos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProspectosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_completo')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('celular')
                    ->icon('heroicon-m-phone')
                    ->searchable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'NUEVO' => 'info',        // Azul
                        'CONTACTADO' => 'warning', // Amarillo
                        'CITA' => 'primary',       // Indigo
                        'APARTADO' => 'success',   // Verde
                        'CLIENTE' => 'success',    // Verde
                        'DESCARTADO' => 'danger',  // Rojo
                        default => 'gray',
                    }),

                TextColumn::make('responsable.name')
                    ->label('Asesor')
                    ->toggleable(),

                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/M/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->options([
                        'NUEVO' => 'Nuevos',
                        'CITA' => 'Con Cita',
                        'DESCARTADO' => 'Descartados',
                    ]),

                SelectFilter::make('usuario_responsable_id')
                    ->relationship('responsable', 'name')
                    ->label('Por Asesor'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
