<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Tables;

use App\Models\ProcesoVenta;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;

class ProcesoVentasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Folio')
                    ->sortable()
                    ->searchable(),

                // Columna inteligente que muestra Prospecto o Cliente
                TextColumn::make('interesado_id') // Usamos un placeholder, el contenido lo define el format
                    ->label('Cliente / Prospecto')
                    ->formatStateUsing(function (ProcesoVenta $record) {
                        if ($record->interesado_type === 'App\\Models\\Prospecto') {
                            return $record->interesado->nombre_completo . " (Prospecto)";
                        }
                        return $record->interesado->nombres . " " . $record->interesado->apellido_paterno . " (Cliente)";
                    })
                    ->icon('heroicon-m-user')
                    ->weight('bold'),

                TextColumn::make('propiedad.numero_credito')
                    ->label('Propiedad')
                    ->description(fn (ProcesoVenta $record) => Str::limit($record->propiedad->direccion_completa, 40))
                    ->searchable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVO', 'VISITA_REALIZADA' => 'info',
                        'SOLICITUD_APARTADO' => 'warning', // Alerta visual
                        'APARTADO', 'DICTAMINADO_R2' => 'success',
                        'CANCELADO' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('vendedor.name')
                    ->label('Vendedor')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->date('d/M/Y')
                    ->label('Iniciado')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->options([
                        'ACTIVO' => 'En Negociación',
                        'APARTADO' => 'Apartados',
                        'CANCELADO' => 'Caídas',
                    ]),
                SelectFilter::make('vendedor_id')
                    ->relationship('vendedor', 'name')
                    ->label('Mi Equipo'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
