<?php

namespace App\Filament\Resources\Comercial\Interaccions\Tables;

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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InteraccionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entidad_nombre')
                    ->label('Nombre Prospecto/Cliente')
                    ->getStateUsing(function ($record) {
                        // Detecta dinámicamente el nombre según el modelo
                        return $record->entidad->nombre_completo
                            ?? $record->entidad->nombre_completo_virtual
                            ?? 'Sin Nombre';
                    })
                    ->searchable() // Esto requerirá un poco más de lógica en el query, pero para visualización funciona
                    ->weight('bold'),
                // Columna principal que usa el Accessor (el Resumen que creamos en el Modelo)
                TextColumn::make('resumen_interaccion')
                    ->label('Interacción')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('entidad_type')
                    ->label('Relacionado con')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => \Illuminate\Support\Str::afterLast($state, '\\'))
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, 'Prospecto') => 'warning',
                        str_contains($state, 'Cliente') => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('fecha_realizada')
                    ->label('Fecha')
                    ->dateTime('d/M/Y H:i')
                    ->sortable(),

                TextColumn::make('usuario.name')
                    ->label('Asesor')
                    ->sortable(),
                IconColumn::make('es_venta_cruzada')
                    ->label('Venta Cruzada')
                    ->boolean()
                    ->trueIcon('heroicon-o-users') // Icono de dos personas (colaboración)
                    ->trueColor('warning') // Color ámbar para destacar
                    ->falseIcon('') // Ocultar si no es venta cruzada para no ensuciar
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'LLAMADA' => 'Llamadas',
                        'VISITA_PROPIEDAD' => 'Visitas',
                        'WHATSAPP' => 'Mensajes',
                    ])
                    ->label('Filtrar por Tipo'),

                SelectFilter::make('usuario_id')
                    ->relationship('usuario', 'name')
                    ->label('Por Asesor'),
                TernaryFilter::make('es_venta_cruzada')
                    ->label('Tipo de Registro')
                    ->placeholder('Todos los registros')
                    ->trueLabel('Solo Ventas Cruzadas (Bonos)')
                    ->falseLabel('Registros Propios'),
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
            ->defaultSort('fecha_realizada', 'desc');
    }
}
