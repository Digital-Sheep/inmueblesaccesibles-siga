<?php

namespace App\Filament\Resources\Comercial\Propiedades\Schemas;

use App\Models\CatEstado;
use App\Models\CatMunicipio;
use App\Models\Cartera;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

use Filament\Notifications\Notification;
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
use Illuminate\Support\Facades\Http;

class PropiedadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Detalles de la Propiedad')
                    ->contained(false)
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
                                                ->helperText('Opcional: Grupo de garantías'),

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
                                    ->description('Dirección completa de la garantía')
                                    ->schema([
                                        Textarea::make('direccion_completa')
                                            ->label('Dirección Completa')
                                            ->rows(2)
                                            ->required()
                                            ->placeholder('Ej: Av. Revolución 1500, Colonia Guadalupe Inn, CDMX')
                                            ->columnSpanFull(),

                                        Grid::make(3)->schema([
                                            Select::make('estado_id')
                                                ->label('Estado')
                                                ->options(CatEstado::orderBy('nombre')->pluck('nombre', 'id'))
                                                ->searchable()
                                                ->native(false)
                                                ->live()
                                                ->afterStateUpdated(fn(Set $set) => $set('municipio_id', null)),

                                            Select::make('municipio_id')
                                                ->label('Municipio / Alcaldía')
                                                ->options(function (Get $get) {
                                                    $estadoId = $get('estado_id');
                                                    if (! $estadoId) return [];
                                                    return CatMunicipio::where('estado_id', $estadoId)
                                                        ->orderBy('nombre')
                                                        ->pluck('nombre', 'id');
                                                })
                                                ->searchable()
                                                ->native(false),

                                            TextInput::make('codigo_postal')
                                                ->label('Código Postal')
                                                ->maxLength(10)
                                                ->placeholder('06600'),
                                        ]),

                                        Grid::make(4)->schema([
                                            TextInput::make('calle')
                                                ->label('Calle')
                                                ->columnSpan(2)
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

                                        // ─── Google Maps + Botón de geocodificación ───
                                        TextInput::make('google_maps_link')
                                            ->label('Link de Google Maps')
                                            ->url()
                                            ->prefixIcon('heroicon-m-globe-alt')
                                            ->placeholder('Pega el link y presiona "Procesar coordenadas" para extraerlas automáticamente.')
                                            ->columnSpanFull()
                                            ->suffixActions([
                                                Action::make('geocodificar')
                                                    ->label('Procesar coordenadas')
                                                    ->color('info')
                                                    ->button()
                                                    ->action(function (Get $get, Set $set) {
                                                        $link = $get('google_maps_link');

                                                        if (! $link) {
                                                            Notification::make()
                                                                ->warning()
                                                                ->title('Sin link')
                                                                ->body('Ingresa primero el link de Google Maps.')
                                                                ->send();
                                                            return;
                                                        }

                                                        // Estrategia 1: extraer coordenadas del link directamente
                                                        $coords = self::extraerCoordsDeLink($link);

                                                        // Estrategia 2: si el link es corto (goo.gl / maps.app.goo.gl), seguir redirect
                                                        if (! $coords && (str_contains($link, 'goo.gl') || str_contains($link, 'maps.app'))) {
                                                            try {
                                                                $response = Http::withOptions(['allow_redirects' => false])
                                                                    ->timeout(5)
                                                                    ->get($link);
                                                                $location = $response->header('Location');
                                                                if ($location) {
                                                                    $coords = self::extraerCoordsDeLink($location);
                                                                }
                                                            } catch (\Throwable $e) {
                                                                // silencioso
                                                            }
                                                        }

                                                        // Estrategia 3: fallback Nominatim con dirección
                                                        if (! $coords) {
                                                            $coords = self::geocodificarConNominatim($get);
                                                        }

                                                        if ($coords) {
                                                            $set('latitud', $coords['lat']);
                                                            $set('longitud', $coords['lng']);

                                                            Notification::make()
                                                                ->success()
                                                                ->title('✅ Coordenadas procesadas')
                                                                ->body("Lat: {$coords['lat']}, Lng: {$coords['lng']}")
                                                                ->send();
                                                        } else {
                                                            Notification::make()
                                                                ->danger()
                                                                ->title('No se pudieron obtener coordenadas')
                                                                ->body('Verifica el link o ingresa las coordenadas manualmente.')
                                                                ->send();
                                                        }
                                                    }),
                                            ]),

                                        // ─── Coordenadas (editables manualmente también) ───
                                        Grid::make(2)->schema([
                                            TextInput::make('latitud')
                                                ->label('Latitud')
                                                ->numeric()
                                                ->step(0.00000001)
                                                ->placeholder('20.6597')
                                                ->prefixIcon('heroicon-o-arrows-up-down')
                                                ->helperText('Se llena automáticamente al procesar el link.')
                                                ->rules(['nullable', 'numeric', 'between:-90,90']),

                                            TextInput::make('longitud')
                                                ->label('Longitud')
                                                ->numeric()
                                                ->step(0.00000001)
                                                ->placeholder('-103.3496')
                                                ->prefixIcon('heroicon-o-arrows-right-left')
                                                ->helperText('Se llena automáticamente al procesar el link.')
                                                ->rules(['nullable', 'numeric', 'between:-180,180']),
                                        ]),
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
                                                ->step(0.01)
                                                ->suffix('m²'),

                                            TextInput::make('construccion_m2')
                                                ->label('Construcción (m²)')
                                                ->numeric()
                                                ->step(0.01)
                                                ->suffix('m²'),

                                            TextInput::make('habitaciones')
                                                ->label('Habitaciones')
                                                ->integer()
                                                ->minValue(0),

                                            TextInput::make('banos')
                                                ->label('Baños')
                                                ->integer()
                                                ->minValue(0),

                                            TextInput::make('estacionamientos')
                                                ->label('Estacionamientos')
                                                ->integer()
                                                ->minValue(0),
                                        ]),
                                    ])
                                    ->columns(1),
                            ]),

                        // ========================================
                        // TAB 3: DATOS LEGALES ⚖️
                        // ========================================
                        Tab::make('Precios y legal')
                            ->icon('heroicon-o-scale')
                            ->schema([
                                Section::make('⚖️ Información Legal')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('etapa_judicial_reportada')
                                                ->label('Etapa Judicial')
                                                ->placeholder('Sentencia Firme'),

                                            TextInput::make('nombre_acreditado')
                                                ->label('Nombre del Acreditado'),

                                            TextInput::make('precio_lista')
                                                ->label('Precio de Lista')
                                                ->numeric()
                                                ->prefix('$'),

                                            TextInput::make('avaluo_banco')
                                                ->label('Avalúo del Banco')
                                                ->numeric()
                                                ->prefix('$'),

                                            TextInput::make('cofinavit_monto')
                                                ->label('Monto COFINAVIT')
                                                ->numeric()
                                                ->prefix('$'),
                                        ]),
                                    ])
                                    ->columns(1),
                            ]),

                        // ========================================
                        // TAB 4: FOTO 📷
                        // ========================================
                        Tab::make('Fotos')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('📷 Galería de Fotos')
                                    ->description('Sube una o varias fotos. Selecciona la categoría de cada una.')
                                    ->schema([
                                        Repeater::make('fotos_repeater')
                                            ->hiddenLabel()
                                            ->schema([
                                                Grid::make(1)->schema([
                                                    FileUpload::make('ruta_archivo')
                                                        ->label('Foto')
                                                        ->disk('public')
                                                        ->directory('propiedades-fotos')
                                                        ->image()
                                                        ->imageEditor()
                                                        ->maxSize(5120)
                                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                        ->required()
                                                        ->fetchFileInformation(false)
                                                        ->columnSpanFull(),

                                                    Grid::make(2)->schema([
                                                        Select::make('categoria')
                                                            ->label('Categoría')
                                                            ->options([
                                                                'FACHADA'  => '🏠 Fachada',
                                                                'INTERIOR' => '🛋️ Interior',
                                                                'PATIO'    => '🌿 Patio / Jardín',
                                                                'PLANO'    => '📐 Plano',
                                                                'LEGAL'    => '📄 Documento Legal',
                                                                'DAMAGE'   => '⚠️ Daño / Deterioro',
                                                            ])
                                                            ->required()
                                                            ->default('FACHADA')
                                                            ->native(false),

                                                        Textarea::make('descripcion')
                                                            ->label('Descripción (opcional)')
                                                            ->rows(2)
                                                            ->maxLength(500),
                                                    ]),
                                                ]),
                                            ])
                                            ->addActionLabel('+ Agregar foto')
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                isset($state['categoria'])
                                                    ? match ($state['categoria']) {
                                                        'FACHADA'  => '🏠 Fachada',
                                                        'INTERIOR' => '🛋️ Interior',
                                                        'PATIO'    => '🌿 Patio / Jardín',
                                                        'PLANO'    => '📐 Plano',
                                                        'LEGAL'    => '📄 Documento Legal',
                                                        'DAMAGE'   => '⚠️ Daño / Deterioro',
                                                        default    => $state['categoria'],
                                                    }
                                                    : 'Nueva foto'
                                            )
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    // ================================================================
    // HELPERS PRIVADOS DE GEOCODIFICACIÓN
    // ================================================================

    /**
     * Extrae latitud y longitud de un link de Google Maps usando regex.
     * Soporta los formatos más comunes.
     */
    private static function extraerCoordsDeLink(string $link): ?array
    {
        $patterns = [
            // @lat,lng,zoom  (formato estándar en URLs largas)
            '/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            // ?q=lat,lng
            '/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            // ?ll=lat,lng
            '/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            // /place/.../@lat,lng
            '/place\/[^@]+@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $link, $matches)) {
                $lat = (float) $matches[1];
                $lng = (float) $matches[2];

                // Validar rango plausible para México
                if ($lat >= 14 && $lat <= 33 && $lng >= -118 && $lng <= -86) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        }

        return null;
    }

    /**
     * Fallback: geocodificación con Nominatim (OpenStreetMap).
     * No requiere API key. Límite: 1 req/seg (uso humano, OK).
     */
    private static function geocodificarConNominatim(Get $get): ?array
    {
        // Intentar con campos estructurados primero
        $partes = array_filter([
            $get('calle') . ' ' . $get('numero_exterior'),
            $get('colonia'),
            $get('municipio_borrador') ?: optional(\App\Models\CatMunicipio::find($get('municipio_id')))->nombre,
            $get('estado_borrador') ?: optional(\App\Models\CatEstado::find($get('estado_id')))->nombre,
            'México',
        ]);

        $query = implode(', ', $partes);

        if (strlen(trim($query)) < 5) return null;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'SIGA-InmuelesAccesibles/1.0',
            ])
                ->timeout(8)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'              => $query,
                    'format'         => 'json',
                    'limit'          => 1,
                    'countrycodes'   => 'mx',
                ]);

            $results = $response->json();

            if (! empty($results)) {
                $lat = (float) $results[0]['lat'];
                $lng = (float) $results[0]['lon'];

                if ($lat >= 14 && $lat <= 33 && $lng >= -118 && $lng <= -86) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        } catch (\Throwable $e) {
            // silencioso — el usuario verá la notificación de fallo
        }

        return null;
    }
}
