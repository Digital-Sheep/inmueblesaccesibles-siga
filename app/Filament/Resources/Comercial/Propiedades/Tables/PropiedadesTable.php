<?php

namespace App\Filament\Resources\Comercial\Propiedades\Tables;

use App\Models\Propiedad;
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
use Illuminate\Support\Str;

class PropiedadesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Identificación Principal (Crédito + Dirección)
                TextColumn::make('numero_credito')
                    ->label('Propiedad')
                    ->description(fn(Propiedad $record) => Str::limit($record->direccion_completa, 50))
                    ->searchable(['numero_credito', 'direccion_completa'])
                    ->sortable()
                    ->weight('bold')
                    ->copyable() // Permite copiar el ID con un clic
                    ->copyMessage('ID copiado'),

                // 2. Precio (Formato Moneda)
                TextColumn::make('precio_venta_sugerido')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),

                // 3. Ubicación
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->sortable()
                    ->searchable(),

                // 4. Semáforo Comercial (Para el Asesor)
                TextColumn::make('estatus_comercial')
                    ->badge()
                    ->label('Estatus Venta')
                    ->color(fn(string $state): string => match ($state) {
                        'DISPONIBLE' => 'success', // Verde
                        'APARTADA' => 'warning',   // Amarillo
                        'VENDIDA' => 'info',       // Azul
                        'BAJA', 'BORRADOR' => 'gray',
                        'EN_REVISION' => 'danger', // Rojo
                        default => 'gray',
                    }),

                // 5. Semáforo Legal (Para Jurídico)
                TextColumn::make('estatus_legal')
                    ->badge()
                    ->label('Jurídico')
                    ->icon(fn(string $state): string => match ($state) {
                        'R2_POSITIVO' => 'heroicon-o-check-circle',
                        'R1_NEGATIVO' => 'heroicon-o-x-circle',
                        'LITIGIO' => 'heroicon-o-scale',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'R2_POSITIVO', 'ADJUDICADA', 'ESCRITURADA' => 'success',
                        'R1_NEGATIVO' => 'danger',
                        'LITIGIO' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                // Filtro Rápido: ¿Qué puedo vender?
                SelectFilter::make('estatus_comercial')
                    ->options([
                        'DISPONIBLE' => '✅ Disponibles',
                        'APARTADA' => '⏳ Apartadas',
                        'VENDIDA' => '🔒 Vendidas',
                    ])
                    ->label('Estatus Venta'),

                // Filtro por Sucursal
                SelectFilter::make('sucursal_id')
                    ->relationship('sucursal', 'nombre')
                    ->label('Sucursal'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            // Orden por defecto: Las más nuevas primero
            ->defaultSort('created_at', 'desc');
    }
}
