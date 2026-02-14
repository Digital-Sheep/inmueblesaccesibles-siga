<?php

namespace App\Filament\Resources\Configuracion\EtapasProcesales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EtapasProcesalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orden')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Etapa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('fase')
                    ->label('Fase jurídica')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'FASE_1' => '📋 Fase 1',
                        'FASE_2' => '⚖️ Fase 2',
                        'FASE_3' => '✅ Fase 3',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('aplica_para_cotizacion')
                    ->label('Cotizador')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fase_cotizacion')
                    ->label('Fase cotización')
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'FASE_1' => 'F1 - 35%',
                        'FASE_2' => 'F2 - 20%',
                        'FASE_3' => 'F3 - 15%',
                        default => 'N/A',
                    })
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'FASE_1' => 'danger',
                        'FASE_2' => 'warning',
                        'FASE_3' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('porcentaje_inversion')
                    ->label('% Inversión')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dias_estimados')
                    ->label('Días estimados')
                    ->suffix(' días')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tipoJuicio.nombre')
                    ->label('Tipo juicio')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('orden')
            ->filters([
                Tables\Filters\TernaryFilter::make('aplica_para_cotizacion')
                    ->label('Usa cotizador')
                    ->placeholder('Todas')
                    ->trueLabel('Solo cotizador')
                    ->falseLabel('Solo jurídico'),

                Tables\Filters\SelectFilter::make('fase')
                    ->label('Fase jurídica')
                    ->options([
                        'FASE_1' => 'Fase 1',
                        'FASE_2' => 'Fase 2',
                        'FASE_3' => 'Fase 3',
                    ]),

                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->modalHeading('Editar etapa procesal')
                    ->modalWidth('6xl')
                    ->closeModalByClickingAway(false)
                    ->mutateDataUsing(function (array $data): array {
                        $user = Auth::user();
                        $data['updated_by'] = $user->id;
                        return $data;
                    }),

                DeleteAction::make()
                    ->button()
                    ->modalHeading('Eliminar etapa procesal')
                    ->modalDescription('¿Confirma que desea eliminar esta etapa procesal? Esta acción no se puede deshacer.')
                    ->modalWidth('md')
                    // ->visible(fn() => Auth::user()->can('configuracion_eliminar')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->can('configuracion_eliminar')),
                ]),
            ]);
    }
}
