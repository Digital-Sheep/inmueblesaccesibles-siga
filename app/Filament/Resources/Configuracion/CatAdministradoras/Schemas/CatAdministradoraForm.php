<?php

namespace App\Filament\Resources\Configuracion\CatAdministradoras\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CatAdministradoraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la Institución')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Razón Social / Nombre'),

                                TextInput::make('abreviatura')
                                    ->maxLength(10)
                                    ->placeholder('Ej. BBVA'),

                                TextInput::make('contacto_principal')
                                    ->label('Nombre de Contacto (Opcional)')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),

                        Toggle::make('activo')
                            ->label('Administradora Activa')
                            ->default(true),
                    ]),
            ]);
    }
}
