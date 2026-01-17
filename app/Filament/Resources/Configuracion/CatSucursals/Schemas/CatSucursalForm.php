<?php

namespace App\Filament\Resources\Configuracion\CatSucursals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CatSucursalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Sucursal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Ej. Matriz Guadalajara'),

                                TextInput::make('abreviatura')
                                    ->label('Código / Abreviatura')
                                    ->maxLength(10)
                                    ->placeholder('GDL')
                                    ->required(),
                            ]),

                        Toggle::make('activo')
                            ->label('Sucursal Activa')
                            ->default(true)
                            ->helperText('Desactivar para ocultar en selectores, pero mantener historial.'),
                    ]),
            ]);
    }
}
