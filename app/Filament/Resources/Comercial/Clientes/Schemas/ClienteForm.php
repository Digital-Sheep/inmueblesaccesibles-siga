<?php

namespace App\Filament\Resources\Comercial\Clientes\Schemas;

use App\Models\CatEstado;
use App\Models\CatMunicipio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SECCIÓN 1: DATOS PERSONALES (Formales)
                Section::make('Identidad del Cliente')
                    ->description('Datos requeridos para escrituración y contratos')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('nombres')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('apellido_paterno')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('apellido_materno')
                                    ->maxLength(100),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('celular')
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                TextInput::make('email')
                                    ->email()
                                    ->unique(ignoreRecord: true),
                                Select::make('estado_civil')
                                    ->options([
                                        'SOLTERO' => 'Soltero(a)',
                                        'CASADO' => 'Casado(a)',
                                        'DIVORCIADO' => 'Divorciado(a)',
                                        'VIUDO' => 'Viudo(a)',
                                        'UNION_LIBRE' => 'Unión Libre',
                                    ]),
                            ]),

                        TextInput::make('ocupacion')
                            ->label('Ocupación')
                            ->maxLength(255),
                    ]),

                // SECCIÓN 2: DATOS FISCALES Y UBICACIÓN
                Section::make('Información Fiscal y Domicilio')
                    ->collapsed() // Colapsado por defecto para no estorbar
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('rfc')
                                    ->label('RFC')
                                    ->maxLength(13)
                                    ->placeholder('AAAA000000XXX'),
                                TextInput::make('curp')
                                    ->label('CURP')
                                    ->maxLength(18),
                            ]),

                        Textarea::make('direccion_fiscal')
                            ->label('Calle y Número')
                            ->rows(2),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('direccion_colonia')
                                    ->label('Colonia'),

                                Select::make('estado_id')
                                    ->label('Estado')
                                    ->options(CatEstado::all()->pluck('nombre', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('municipio_id', null)),

                                Select::make('municipio_id')
                                    ->label('Municipio')
                                    ->options(
                                        fn(Get $get) =>
                                        CatMunicipio::where('estado_id', $get('estado_id'))->pluck('nombre', 'id')
                                    )
                                    ->searchable(),

                                TextInput::make('codigo_postal')
                                    ->numeric()
                                    ->maxLength(5),
                            ]),
                    ]),

                // SECCIÓN 3: GESTIÓN INTERNA
                Section::make('Asignación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('sucursal_id')
                                    ->relationship('sucursal', 'nombre')
                                    ->required(),

                                Select::make('usuario_responsable_id')
                                    ->relationship('responsable', 'name')
                                    ->label('Ejecutivo Responsable')
                                    ->default(fn() => Auth::id())
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
