<?php

namespace App\Filament\Resources\Configuracion\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->email), // Muestra el email abajo del nombre

                // Muestra los roles como etiquetas de colores
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Roles')
                    ->color(fn($state) => match ($state) {
                        'Super Admin' => 'danger',
                        'Gerente' => 'warning',
                        'Asesor' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('sucursal.nombre')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('activo')
                    ->boolean()
                    ->label('Acceso'),

                TextColumn::make('created_at')
                    ->date()
                    ->label('Alta'),
            ])
            ->filters([
                // Filtro por Rol
                SelectFilter::make('roles')
                    ->relationship('roles', 'name'),

                // Filtro por Sucursal
                SelectFilter::make('sucursal')
                    ->relationship('sucursal', 'nombre'),

                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
