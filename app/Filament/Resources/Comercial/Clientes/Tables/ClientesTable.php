<?php

namespace App\Filament\Resources\Comercial\Clientes\Tables;

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

class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_completo_virtual')
                    ->label('Cliente')
                    ->searchable(['nombres', 'apellido_paterno', 'apellido_materno'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('celular')
                    ->icon('heroicon-m-phone')
                    ->searchable(),

                TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('procesos_venta_count')
                    ->counts('procesosVenta')
                    ->label('Procesos activos')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('sucursal.nombre')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('responsable.name')
                    ->label('Ejecutivo')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('sucursal_id')
                    ->relationship('sucursal', 'nombre')
                    ->label('Sucursal'),

                SelectFilter::make('usuario_responsable_id')
                    ->relationship('responsable', 'name')
                    ->label('Por Ejecutivo'),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->label('Detalles')
                    ->modalHeading('Datos del cliente')
                    ->modalWidth('4xl')
                    ->slideOver()
                    ->button(),
            ])
            // ->toolbarActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //     ]),
            // ])
            ->defaultSort('created_at', 'desc');
    }
}
