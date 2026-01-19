<?php

namespace App\Filament\Resources\Comercial\EventoAgendas\Tables;

use App\Models\EventoAgenda;
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
use Illuminate\Support\Str;

class EventoAgendasTable
{
    public static function configure(Table $table): Table
    {
        return $table;
        // return $table
        //     ->columns([
        //         TextColumn::make('titulo')
        //             ->searchable()
        //             ->sortable()
        //             ->weight('bold'),

        //         TextColumn::make('fecha_inicio')
        //             ->label('Inicio')
        //             ->dateTime('d/M/Y H:i')
        //             ->sortable(),

        //         TextColumn::make('participante.nombre_completo')
        //             ->label('Participante')
        //             ->description(fn(EventoAgenda $record) => Str::afterLast($record->participante_type, '\\'))
        //             ->searchable(),

        //         TextColumn::make('tipo')
        //             ->badge()
        //             ->color(fn(string $state): string => match ($state) {
        //                 'CITA_VISITA' => 'success',
        //                 'LLAMADA' => 'info',
        //                 'FIRMA_CONTRATO' => 'primary',
        //                 'REUNION_INTERNA' => 'gray',
        //                 default => 'warning',
        //             })
        //             ->sortable()
        //             ->toggleable(),

        //         TextColumn::make('usuario.name')
        //             ->label('Asignado a')
        //             ->sortable()
        //             ->toggleable(),
        //     ])
        //     ->filters([
        //         SelectFilter::make('tipo')
        //             ->options([
        //                 'CITA_VISITA' => 'Citas',
        //                 'LLAMADA' => 'Llamadas',
        //                 'FIRMA_CONTRATO' => 'Firmas',
        //             ]),
        //         SelectFilter::make('usuario_id')
        //             ->relationship('usuario', 'name')
        //             ->label('Por Usuario'),
        //     ])
        //     ->actions([
        //         ViewAction::make(),
        //         EditAction::make(),
        //         DeleteAction::make(),
        //     ])
        //     ->bulkActions([
        //         BulkActionGroup::make([
        //             DeleteBulkAction::make(),
        //         ]),
        //     ])
        //     ->defaultSort('fecha_inicio', 'asc');
    }
}
