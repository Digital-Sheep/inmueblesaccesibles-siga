<?php

namespace App\Filament\Resources\Comercial\Propiedades\Schemas;

use App\Models\CatEstado;
use App\Models\CatMunicipio;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Input\Input;

class PropiedadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Detalles de la Propiedad')
                    ->tabs([
                        // --- PESTAÑA 1: UBICACIÓN ---
                        Tab::make('Ubicación')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('sucursal_id')
                                            ->relationship('sucursal', 'nombre')
                                            ->required()
                                            ->label('Sucursal asignada'),

                                        TextInput::make('numero_credito')
                                            ->label('No. Crédito / ID')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Textarea::make('direccion_completa')
                                    ->label('Dirección completa')
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

                                        TextInput::make('calle')
                                            ->columnSpan(3),

                                        TextInput::make('colonia'),

                                        TextInput::make('numero_exterior'),

                                        TextInput::make('numero_interior'),
                                    ]),

                                TextInput::make('google_maps_link')
                                    ->label('Link de Google Maps')
                                    ->url()
                                    ->prefixIcon('heroicon-m-globe-alt')
                                    ->columnSpanFull(),
                            ]),

                        // --- PESTAÑA 2: PRECIOS Y NEGOCIO ---
                        Tab::make('Precios y estatus')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('precio_lista')
                                            ->label('Precio Lista (Banco)')
                                            ->prefix('$')
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->placeholder('0.00'),
                                    ]),

                                Section::make('Estatus de control')
                                    ->schema([
                                        Select::make('estatus_comercial')
                                            ->options([
                                                'BORRADOR' => 'Borrador',
                                                'EN_REVISION' => 'En Revisión (Dirección)',
                                                'DISPONIBLE' => 'Disponible',
                                                'APARTADA' => 'Apartada',
                                                'VENDIDA' => 'Vendida',
                                                'BAJA' => 'Baja / Cancelada',
                                            ])
                                            ->default('BORRADOR')
                                            ->native(false)
                                            ->disabled()
                                            ->dehydrated()
                                            ->hidden(function (string $operation, Get $get) {
                                                if ($operation === 'create') {
                                                    return true;
                                                }
                                            }),

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
                                            ->default('SIN_REVISAR')
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->native(false),

                                        TextInput::make('nombre_acreditado')
                                            ->label('Nombre del Acreditado / Deudor')
                                            ->maxLength(255)
                                            ->visible(
                                                function () {
                                                    /** @var \App\Models\User $user */
                                                    $user = Auth::user();

                                                    return $user && $user->can('propiedades_ver_datos_sensibles');
                                                }
                                            ),
                                    ])->columns(2),
                            ]),

                        // --- PESTAÑA 3: CARACTERÍSTICAS ---
                        Tab::make('Características')
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

                        // --- PESTAÑA 4: GALERÍA Y ARCHIVOS ---
                        Tab::make('Galería y archivos')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Repeater::make('archivos')
                                    ->relationship()
                                    ->label('Fotos y documentos de la propiedad')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                FileUpload::make('ruta_archivo')
                                                    ->label('Evidencia / Foto')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->directory('propiedades-fotos')
                                                    ->storeFileNamesIn('nombre_original')
                                                    ->required()
                                                    ->columnSpan(1),

                                                Group::make()
                                                    ->schema([
                                                        Select::make('categoria')
                                                            ->label('Categoría')
                                                            ->options([
                                                                'FACHADA' => '🏠 Fachada Principal',
                                                                'INTERIOR' => '🛋️ Interiores',
                                                                'PATIO' => '🌳 Patio / Jardín',
                                                                'PLANO' => '🗺️ Plano / Croquis',
                                                                'DAMAGE' => '⚠️ Daños / Reparaciones',
                                                                'LEGAL' => '⚖️ Documento Legal (Público)',
                                                            ])
                                                            ->required()
                                                            ->default('INTERIOR'),

                                                        Hidden::make('tipo_mime')
                                                            ->default('image/jpeg'),
                                                    ])
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->grid(1)
                                    ->defaultItems(0)
                                    ->addActionLabel('Subir nueva foto')
                                    ->reorderableWithButtons()
                                    ->collapsible(),
                            ])
                            ->columnSpanFull()
                    ])
            ]);
    }
}
