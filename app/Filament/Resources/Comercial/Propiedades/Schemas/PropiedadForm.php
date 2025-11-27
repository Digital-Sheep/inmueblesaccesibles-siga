<?php

namespace App\Filament\Resources\Comercial\Propiedades\Schemas;

use App\Models\CatEstado;
use App\Models\CatMunicipio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PropiedadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Detalles de la Propiedad')
                    ->tabs([
                        // --- PESTAÑA 1: UBICACIÓN ---
                        Tabs\Tab::make('Ubicación')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('sucursal_id')
                                            ->relationship('sucursal', 'nombre')
                                            ->required()
                                            ->label('Sucursal Asignada'),

                                        TextInput::make('numero_credito')
                                            ->label('No. Crédito / ID')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Textarea::make('direccion_completa')
                                    ->label('Dirección Completa (Como viene en el Excel)')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->required(),

                                Grid::make(3)
                                    ->schema([
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

                                        TextInput::make('colonia'),
                                        TextInput::make('calle'),
                                        TextInput::make('numero_exterior'),
                                    ]),

                                TextInput::make('google_maps_link')
                                    ->label('Link de Google Maps')
                                    ->url()
                                    ->prefixIcon('heroicon-m-globe-alt')
                                    ->columnSpanFull(),
                            ]),

                        // --- PESTAÑA 2: PRECIOS Y NEGOCIO ---
                        Tabs\Tab::make('Precios y Estatus')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('precio_lista')
                                            ->label('Precio Lista (Banco)')
                                            ->prefix('$')
                                            ->numeric(),

                                        TextInput::make('precio_venta_sugerido')
                                            ->label('Precio Venta Sugerido')
                                            ->prefix('$')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('precio_minimo')
                                            ->label('Precio Mínimo (Piso)')
                                            ->prefix('$')
                                            ->numeric()
                                            ->password()
                                            ->revealable(),
                                    ]),

                                Section::make('Semáforos de Control')
                                    ->schema([
                                        Select::make('estatus_comercial')
                                            ->options([
                                                'BORRADOR' => 'Borrador (Oculto)',
                                                'EN_REVISION' => 'En Revisión (Dirección)',
                                                'DISPONIBLE' => 'Disponible (Venta)',
                                                'APARTADA' => 'Apartada',
                                                'VENDIDA' => 'Vendida',
                                                'BAJA' => 'Baja / Cancelada',
                                            ])
                                            ->default('BORRADOR')
                                            ->selectablePlaceholder(false)
                                            ->native(false),

                                        Select::make('estatus_legal')
                                            ->label('Estatus Jurídico (Informativo)')
                                            ->options([
                                                'SIN_REVISAR' => 'Sin Revisar',
                                                'R1_NEGATIVO' => 'R1 - Negativo (Riesgo)',
                                                'R2_POSITIVO' => 'R2 - Positivo (Viable)',
                                                'LITIGIO' => 'En Litigio Activo',
                                                'ADJUDICADA' => 'Adjudicada',
                                                'ESCRITURADA' => 'Escriturada',
                                            ])
                                            ->disabled()
                                            ->dehydrated(),
                                    ])->columns(2),
                            ]),

                        // --- PESTAÑA 3: CARACTERÍSTICAS ---
                        Tabs\Tab::make('Características')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('tipo_inmueble')
                                            ->options([
                                                'CASA' => 'Casa',
                                                'DEPARTAMENTO' => 'Departamento',
                                                'TERRENO' => 'Terreno',
                                                'LOCAL' => 'Local Comercial',
                                            ]),
                                        TextInput::make('terreno_m2')->numeric()->suffix('m²'),
                                        TextInput::make('construccion_m2')->numeric()->suffix('m²'),
                                        TextInput::make('habitaciones')->numeric(),
                                        TextInput::make('banos')->numeric()->label('Baños'),
                                        TextInput::make('estacionamientos')->numeric(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }
}
