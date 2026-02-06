<?php

namespace App\Filament\Resources\Configuracion\TabuladorCostos\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class TabuladorCostosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tamaño de propiedad')
                    ->description('Define el rango de metros cuadrados')
                    ->schema([
                        Select::make('tamano_propiedad')
                            ->label('Categoría')
                            ->options([
                                'CHICA' => 'Chica (0-80 m²)',
                                'MEDIANA' => 'Mediana (81-150 m²)',
                                'GRANDE' => 'Grande (151-250 m²)',
                                'MUY_GRANDE' => 'Muy Grande (251+ m²)',
                            ])
                            ->required()
                            ->native(false)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(1),

                Section::make('Costos operativos')
                    ->description('Ajusta los costos según el tamaño de la propiedad')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('costo_remodelacion')
                                    ->label('Remodelación')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                TextInput::make('costo_luz')
                                    ->label('Luz')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                    TextInput::make('costo_agua')
                                    ->label('Agua')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                TextInput::make('costo_predial')
                                    ->label('Predial')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                TextInput::make('costo_gastos_juridicos')
                                    ->label('Gastos Jurídicos')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Total')
                    ->schema([
                        Placeholder::make('total_calculado')
                            ->label('Total de costos operativos')
                            ->content(function ($get) {
                                $total =
                                    floatval(str_replace(',', '', $get('costo_remodelacion') ?? 0)) +
                                    floatval(str_replace(',', '', $get('costo_luz') ?? 0)) +
                                    floatval(str_replace(',', '', $get('costo_agua') ?? 0)) +
                                    floatval(str_replace(',', '', $get('costo_predial') ?? 0)) +
                                    floatval(str_replace(',', '', $get('costo_gastos_juridicos') ?? 0));

                                return '$' . number_format($total, 2);
                            }),
                    ]),
            ]);
    }
}
