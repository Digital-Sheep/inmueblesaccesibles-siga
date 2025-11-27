<?php

namespace App\Filament\Resources\Finanzas\Pagos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Illuminate\Support\Facades\Auth;

class PagoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle del Ingreso')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('concepto')
                                    ->options([
                                        'APARTADO' => 'Apartado ($10,000)',
                                        'ENGANCHE' => 'Enganche (50%)',
                                        'LIQUIDACION' => 'Liquidación Final',
                                        'ABONO' => 'Abono a Capital',
                                    ])
                                    ->required(),

                                TextInput::make('monto')
                                    ->label('Monto Recibido')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('metodo_pago')
                                    ->label('Método de Pago')
                                    ->options([
                                        'TRANSFERENCIA' => 'Transferencia SPEI',
                                        'EFECTIVO' => 'Efectivo (Caja)',
                                        'CHEQUE' => 'Cheque',
                                    ])
                                    ->required(),

                                DatePicker::make('created_at')
                                    ->label('Fecha de Pago')
                                    ->default(now())
                                    ->required(),
                            ]),

                        FileUpload::make('comprobante_url')
                            ->label('Comprobante / Voucher')
                            ->image() // Permite ver vista previa si es imagen
                            ->disk('public')
                            ->directory('comprobantes')
                            ->required()
                            ->columnSpanFull()
                            ->openable(), // Permite abrirlo en otra pestaña
                    ])->columnSpan(2),

                Section::make('Validación Financiera (GAD)')
                    ->schema([
                        Select::make('estatus')
                            ->options([
                                'PENDIENTE' => '🟡 Pendiente',
                                'VALIDADO' => '🟢 Validado (Fondos en Cuenta)',
                                'RECHAZADO' => '🔴 Rechazado',
                            ])
                            ->default('PENDIENTE')
                            ->disabled(function () {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return ! $user->hasRole(['Super_Admin', 'Administracion']);
                            })
                            ->dehydrated(), // Guarda el valor aunque esté disabled
                        Select::make('validado_por_id')
                            ->relationship('validadoPor', 'name')
                            ->label('Validado por')
                            ->disabled(),

                        DatePicker::make('fecha_validacion')
                            ->label('Fecha Validación')
                            ->disabled(),
                    ])->columnSpan(1),
            ])->columns(3);
    }
}
