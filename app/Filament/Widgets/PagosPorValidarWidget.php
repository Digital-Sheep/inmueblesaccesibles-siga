<?php

namespace App\Filament\Widgets;

use App\Models\Pago;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PagosPorValidarWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole([ 'DGE', 'Super_Admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('💰 Pagos Pendientes de Validar')
            ->query(
                Pago::query()
                    ->where('estatus', 'PENDIENTE')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/M/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('concepto')
                    ->label('Concepto')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'APARTADO' => 'info',
                        'ENGANCHE' => 'warning',
                        'LIQUIDACION' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->money('MXN')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('procesoVenta.propiedad.numero_credito')
                    ->label('Propiedad')
                    ->searchable(),

                Tables\Columns\TextColumn::make('procesoVenta.vendedor.name')
                    ->label('Asesor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('metodo_pago')
                    ->label('Método'),
            ])
            ->defaultPaginationPageOption(10)
            ->actions([
                Action::make('ver_comprobante')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->url(fn($record) => asset('storage/' . $record->comprobante_url))
                    ->openUrlInNewTab(),

                Action::make('validar')
                    ->label('Validar')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Validar este pago?')
                    ->modalDescription('Confirma que el pago ha sido verificado y es correcto.')
                    ->action(function ($record) {
                        // Esta lógica debería estar en PagosTable, solo es referencia
                        $record->update(['estatus' => 'VALIDADO']);
                    }),
            ]);
    }
}
