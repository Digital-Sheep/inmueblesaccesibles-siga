<?php

namespace App\Filament\Resources\Comercial\Propiedades\Schemas;

use App\Models\CatEstado;
use App\Models\CatMunicipio;
use App\Models\Cartera;

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

class PropiedadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Detalles de la Propiedad')
                    ->contained(false) // ✨ Mejora visual: tabs sin borde
                    ->tabs([

                        // ========================================
                        // TAB 1: INFORMACIÓN GENERAL 🗺️
                        // ========================================
                        Tab::make('Información General')
                            ->icon('heroicon-o-map-pin')
                            ->schema([

                                // SECCIÓN: Asignación Administrativa
                                Section::make('📋 Asignación Administrativa')
                                    ->description('Sucursal y cartera de origen')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('sucursal_id')
                                                ->relationship('sucursal', 'nombre')
                                                ->required()
                                                ->label('Sucursal')
                                                ->native(false)
                                                ->searchable()
                                                ->preload(),

                                            Select::make('cartera_id')
                                                ->label('Cartera')
                                                ->options(
                                                    Cartera::query()
                                                        ->orderBy('nombre')
                                                        ->pluck('nombre', 'id')
                                                )
                                                ->searchable()
                                                ->native(false)
                                                ->placeholder('Sin cartera asignada')
                                                ->helperText('Opcional: Grupo de propiedades'),

                                            TextInput::make('numero_credito')
                                                ->label('No. Crédito / Folio')
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(255)
                                                ->prefixIcon('heroicon-o-identification')
                                                ->placeholder('Ej: 21700-ABC-001'),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->columns(1),

                                // SECCIÓN: Ubicación
                                Section::make('📍 Ubicación del Inmueble')
                                    ->description('Dirección completa de la propiedad')
                                    ->schema([
                                        Textarea::make('direccion_completa')
                                            ->label('Dirección Completa')
                                            ->rows(2)
                                            ->required()
                                            ->placeholder('Ej: Av. Revolución 1500, Col. Guadalupe Inn')
                                            ->columnSpanFull(),

                                        Grid::make(3)->schema([
                                            Select::make('estado_id')
                                                ->label('Estado')
                                                ->options(CatEstado::all()->pluck('nombre', 'id'))
                                                ->searchable()
                                                ->native(false)
                                                ->live()
                                                ->afterStateUpdated(fn(Set $set) => $set('municipio_id', null))
                                                ->prefixIcon('heroicon-o-map'),

                                            Select::make('municipio_id')
                                                ->label('Municipio / Alcaldía')
                                                ->options(
                                                    fn(Get $get) =>
                                                    CatMunicipio::where('estado_id', $get('estado_id'))
                                                        ->pluck('nombre', 'id')
                                                )
                                                ->searchable()
                                                ->native(false)
                                                ->disabled(fn(Get $get) => !$get('estado_id'))
                                                ->prefixIcon('heroicon-o-building-office-2'),

                                            TextInput::make('codigo_postal')
                                                ->label('C.P.')
                                                ->numeric()
                                                ->maxLength(5)
                                                ->placeholder('00000')
                                                ->prefixIcon('heroicon-o-envelope'),
                                        ]),

                                        Grid::make(3)->schema([
                                            TextInput::make('calle')
                                                ->label('Calle')
                                                ->columnSpan(3)
                                                ->placeholder('Av. Revolución'),

                                            TextInput::make('colonia')
                                                ->label('Colonia / Fraccionamiento')
                                                ->columnSpan(2)
                                                ->placeholder('Guadalupe Inn'),

                                            TextInput::make('numero_exterior')
                                                ->label('Núm. Ext.')
                                                ->placeholder('1500'),

                                            TextInput::make('numero_interior')
                                                ->label('Núm. Int.')
                                                ->placeholder('Depto 3'),
                                        ]),

                                        TextInput::make('google_maps_link')
                                            ->label('Link de Google Maps')
                                            ->url()
                                            ->prefixIcon('heroicon-m-globe-alt')
                                            ->placeholder('https://maps.google.com/...')
                                            ->helperText('Opcional: Para localización en mapa')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->columns(1),
                            ]),

                        // ========================================
                        // TAB 2: CARACTERÍSTICAS 🏠
                        // ========================================
                        Tab::make('Características')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Section::make('🏠 Características del Inmueble')
                                    ->description('Tipo, superficies y distribución')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('tipo_inmueble')
                                                ->label('Tipo de Inmueble')
                                                ->options([
                                                    'CASA' => '🏡 Casa',
                                                    'DEPARTAMENTO' => '🏢 Departamento',
                                                    'TERRENO' => '🌳 Terreno',
                                                    'LOCAL' => '🏪 Local Comercial',
                                                ])
                                                ->native(false)
                                                ->prefixIcon('heroicon-o-home-modern'),

                                            TextInput::make('terreno_m2')
                                                ->label('Terreno (m²)')
                                                ->numeric()
                                                ->suffix('m²')
                                                ->placeholder('120')
                                                ->prefixIcon('heroicon-o-square-3-stack-3d'),

                                            TextInput::make('construccion_m2')
                                                ->label('Construcción (m²)')
                                                ->numeric()
                                                ->suffix('m²')
                                                ->placeholder('85')
                                                ->prefixIcon('heroicon-o-building-office'),
                                        ]),

                                        Grid::make(3)->schema([
                                            TextInput::make('habitaciones')
                                                ->label('Habitaciones')
                                                ->numeric()
                                                ->default(0)
                                                ->prefixIcon('heroicon-o-home')
                                                ->placeholder('3'),

                                            TextInput::make('banos')
                                                ->label('Baños')
                                                ->numeric()
                                                ->default(0)
                                                ->prefixIcon('heroicon-o-inbox')
                                                ->placeholder('2'),

                                            TextInput::make('estacionamientos')
                                                ->label('Estacionamientos')
                                                ->numeric()
                                                ->default(0)
                                                ->prefixIcon('heroicon-o-truck')
                                                ->placeholder('1'),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->columns(1),
                            ]),

                        // ========================================
                        // TAB 3: PRECIOS Y LEGAL 💰⚖️
                        // ========================================
                        Tab::make('Precios y Legal')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([

                                // SECCIÓN: Datos Financieros
                                Section::make('💰 Datos Financieros')
                                    ->description('Valores de referencia de la propiedad')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('precio_lista')
                                                ->label('Precio de Lista')
                                                ->prefix('$')
                                                ->numeric()
                                                ->mask(RawJs::make('$money($input)'))
                                                ->stripCharacters(',')
                                                ->placeholder('850,000')
                                                ->helperText('Precio en la administradora'),

                                            TextInput::make('precio_valor_comercial')
                                                ->label('Valor Comercial')
                                                ->prefix('$')
                                                ->numeric()
                                                ->mask(RawJs::make('$money($input)'))
                                                ->stripCharacters(',')
                                                ->placeholder('1,200,000')
                                                ->helperText('Valor de mercado actual'),
                                        ]),

                                        Grid::make(2)->schema([
                                            TextInput::make('avaluo_banco')
                                                ->label('Avalúo del Banco')
                                                ->prefix('$')
                                                ->numeric()
                                                ->mask(RawJs::make('$money($input)'))
                                                ->stripCharacters(',')
                                                ->placeholder('600,000')
                                                ->helperText('Avalúo reportado por la administradora'),

                                            TextInput::make('cofinavit_monto')
                                                ->label('COFINAVIT')
                                                ->prefix('$')
                                                ->numeric()
                                                ->mask(RawJs::make('$money($input)'))
                                                ->stripCharacters(',')
                                                ->placeholder('150,000')
                                                ->helperText('Subcuenta de vivienda (si aplica)'),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->columns(1),

                                // SECCIÓN: Información Legal
                                Section::make('⚖️ Información Legal')
                                    ->description('Estado procesal y jurídico')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('etapa_judicial_reportada')
                                                ->label('Etapa Judicial (Reporte)')
                                                ->placeholder('Ej: Sentencia Firme')
                                                ->helperText('Etapa reportada en documentos'),

                                            TextInput::make('segunda_etapa')
                                                ->label('Segunda Etapa / Detalle')
                                                ->placeholder('Ej: En Adjudicación')
                                                ->helperText('Información complementaria'),

                                            Select::make('estatus_legal')
                                                ->label('Estatus Legal (Sistema)')
                                                ->options([
                                                    'SIN_REVISAR' => '⏳ Sin Revisar',
                                                    'R2_POSITIVO' => '✅ R2 - Positivo (Viable)',
                                                    'R1_NEGATIVO' => '⚠️ R1 - Negativo (Requiere Cambio)',
                                                    'LITIGIO' => '⚖️ En Litigio Activo',
                                                    'ADJUDICADA' => '🏆 Adjudicada',
                                                    'ESCRITURADA' => '📜 Escriturada',
                                                ])
                                                ->default('SIN_REVISAR')
                                                ->disabled() // Solo lectura, cambia jurídico
                                                ->dehydrated(true)
                                                ->native(false)
                                                ->helperText('Actualizado por el área jurídica'),

                                            TextInput::make('nombre_acreditado')
                                                ->label('Nombre del Acreditado / Deudor')
                                                ->maxLength(255)
                                                ->placeholder('Datos sensibles')
                                                ->visible(
                                                    function () {
                                                        /** @var \App\Models\User $user */
                                                        $user = Auth::user();
                                                        return $user && $user->can('propiedades_ver_datos_sensibles');
                                                    }
                                                ),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->columns(1),
                            ]),

                        // ========================================
                        // TAB 4: GALERÍA 📸
                        // ========================================
                        Tab::make('Galería')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('📸 Fotos y Documentos')
                                    ->description('Sube evidencias fotográficas y documentos de la propiedad')
                                    ->schema([
                                        Repeater::make('archivos')
                                            ->relationship()
                                            ->label('')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    FileUpload::make('ruta_archivo')
                                                        ->label('Archivo / Foto')
                                                        ->image()
                                                        ->imageEditor()
                                                        ->imageEditorAspectRatios([
                                                            '16:9',
                                                            '4:3',
                                                            '1:1',
                                                        ])
                                                        ->directory('propiedades-fotos')
                                                        ->storeFileNamesIn('nombre_original')
                                                        ->required()
                                                        ->maxSize(5120) // 5MB
                                                        ->acceptedFileTypes(['image/*'])
                                                        ->columnSpan(1),

                                                    Group::make()->schema([
                                                        Select::make('categoria')
                                                            ->label('Categoría')
                                                            ->options([
                                                                'FACHADA' => '🏠 Fachada Principal',
                                                                'INTERIOR' => '🛋️ Interiores',
                                                                'PATIO' => '🌳 Patio / Jardín',
                                                                'PLANO' => '🗺️ Plano / Croquis',
                                                                'DAMAGE' => '⚠️ Daños / Reparaciones',
                                                                'LEGAL' => '⚖️ Documento Legal',
                                                            ])
                                                            ->required()
                                                            ->default('INTERIOR')
                                                            ->native(false),

                                                        Textarea::make('descripcion')
                                                            ->label('Descripción')
                                                            ->rows(2)
                                                            ->placeholder('Describe qué se muestra en esta imagen...'),

                                                        Hidden::make('tipo_mime')
                                                            ->default('image/jpeg'),
                                                    ])->columnSpan(1),
                                                ]),
                                            ])
                                            ->grid(1)
                                            ->defaultItems(0)
                                            ->addActionLabel('➕ Subir nueva foto')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(fn(array $state): ?string => $state['categoria'] ?? 'Nueva foto'),
                                    ])
                                    ->collapsible()
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
