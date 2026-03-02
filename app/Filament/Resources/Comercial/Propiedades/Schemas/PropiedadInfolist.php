<?php

namespace App\Filament\Resources\Comercial\Propiedades\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

use Filament\Schemas\Schema;

use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

use Illuminate\Support\Facades\Auth;

class PropiedadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // HEADER
                Section::make()
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                Group::make()
                                    ->columnSpan(['default' => 12, 'md' => 8])
                                    ->schema([
                                        TextEntry::make('numero_credito')
                                            ->label('No. Crédito')
                                            ->size(TextSize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->copyable()
                                            ->icon('heroicon-o-identification'),

                                        TextEntry::make('direccion_completa')
                                            ->label('Dirección'),
                                    ]),

                                Group::make()
                                    ->columnSpan(['default' => 12, 'md' => 4])
                                    ->schema([
                                        TextEntry::make('estatus_comercial')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'DISPONIBLE', 'DISPONIBLE' => 'success',
                                                'APARTADA' => 'warning',
                                                'VENDIDA' => 'info',
                                                'BAJA', 'BORRADOR' => 'gray',
                                                default => 'gray',
                                            }),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),

                // TABS PRINCIPALES
                Tabs::make('Información')
                    ->tabs([
                        // TAB 1: INFORMACIÓN GENERAL
                        Tab::make('Información General')
                            ->icon('heroicon-o-home')
                            ->schema([
                                // PRECIOS
                                Section::make('Precio de Venta')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('precio_lista')
                                                    ->label('Precio Lista (Banco)')
                                                    ->money('MXN'),

                                                TextEntry::make('precio_sin_remodelacion')
                                                    ->label('Precio Sin Remodelación')
                                                    ->money('MXN')
                                                    ->visible(fn($record) => $record->precio_calculado),

                                                TextEntry::make('precio_venta_sugerido')
                                                    ->label('Precio Venta Sugerido')
                                                    ->money('MXN')
                                                    ->weight(FontWeight::Bold)
                                                    ->color('success')
                                                    ->visible(fn($record) => $record->precio_calculado),

                                                TextEntry::make('precio_venta_con_descuento')
                                                    ->label('Precio Con Descuento')
                                                    ->money('MXN')
                                                    ->visible(fn($record) => $record->precio_calculado),

                                                TextEntry::make('porcentaje_descuento')
                                                    ->label('% Descuento')
                                                    ->suffix('%')
                                                    ->visible(fn($record) => $record->precio_calculado),

                                                TextEntry::make('porcentaje_utilidad')
                                                    ->label('% Utilidad')
                                                    ->suffix('%')
                                                    ->weight(FontWeight::Bold)
                                                    ->color('warning')
                                                    ->visible(fn($record) => $record->precio_calculado),
                                            ]),

                                        ViewEntry::make('leyenda_precio_view')
                                            ->label('')
                                            ->view('filament.infolists.leyenda-precio')
                                            ->visible(fn($record) => $record->leyenda_precio !== null),
                                    ])
                                    ->visible(fn($record) => $record->precio_calculado || $record->precio_lista)
                                    ->collapsible(),

                                Section::make('📊 Historial de Cotizaciones')
                                    ->schema([
                                        RepeatableEntry::make('cotizaciones')
                                            ->label('')
                                            ->schema([
                                                TextEntry::make('version')
                                                    ->label('Versión')
                                                    ->badge()
                                                    ->color(fn($record) => $record->activa ? 'success' : 'gray'),

                                                TextEntry::make('created_at')
                                                    ->label('Fecha')
                                                    ->dateTime('d/m/Y H:i'),

                                                TextEntry::make('precio_venta_sugerido')
                                                    ->label('Precio Sugerido')
                                                    ->money('MXN')
                                                    ->weight(FontWeight::Bold),

                                                TextEntry::make('precio_venta_con_descuento')
                                                    ->label('Con Descuento')
                                                    ->money('MXN'),

                                                TextEntry::make('porcentaje_utilidad')
                                                    ->label('Utilidad')
                                                    ->suffix('%')
                                                    ->color('success'),

                                                TextEntry::make('activa')
                                                    ->label('Estado')
                                                    ->badge()
                                                    ->formatStateUsing(fn($state) => $state ? 'ACTIVA' : 'HISTÓRICA')
                                                    ->color(fn($state) => $state ? 'success' : 'gray'),
                                            ])
                                            ->columns(6)
                                            ->contained(false),
                                    ])
                                    ->visible(
                                        function ($record) {
                                            /** @var \App\Models\User $user */
                                            $user = Auth::user();

                                            return $record->cotizaciones()->exists() &&
                                                $user->can('propiedades_ver_datos_sensibles');
                                        }
                                    )
                                    ->collapsible()
                                    ->collapsed(),

                                // UBICACIÓN
                                Section::make('Ubicación')
                                    ->schema([
                                        Grid::make(3) // ← 3 columnas
                                            ->schema([
                                                TextEntry::make('estado.nombre')
                                                    ->label('Estado'),
                                                TextEntry::make('municipio.nombre')
                                                    ->label('Municipio'),
                                                TextEntry::make('codigo_postal')
                                                    ->label('C.P.'),
                                            ]),

                                        Grid::make(3) // ← 4 columnas
                                            ->schema([
                                                TextEntry::make('calle'),
                                                TextEntry::make('numero_exterior')
                                                    ->label('Núm. Ext.'),
                                                TextEntry::make('numero_interior')
                                                    ->label('Núm. Int.')
                                                    ->placeholder('N/A'),
                                            ]),

                                        Grid::make(3) // ← 2 columnas
                                            ->schema([
                                                TextEntry::make('colonia'),
                                                TextEntry::make('fraccionamiento')
                                                    ->placeholder('No especificado'),
                                            ]),

                                        TextEntry::make('google_maps_link')
                                            ->label('Google Maps')
                                            ->url(fn($state) => $state)
                                            ->openUrlInNewTab()
                                            ->placeholder('No disponible')
                                            ->icon('heroicon-o-map-pin')
                                            ->columnSpanFull(), // ← Ocupa todo el ancho
                                    ])
                                    ->columns(1) // ← La sección en sí es de 1 columna
                                    ->collapsible(),

                                // CARACTERÍSTICAS
                                Section::make('Características')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('tipo_inmueble')
                                                    ->badge(),
                                                TextEntry::make('terreno_m2')
                                                    ->suffix(' m²')
                                                    ->placeholder('N/D'),
                                                TextEntry::make('construccion_m2')
                                                    ->suffix(' m²')
                                                    ->placeholder('N/D'),
                                                TextEntry::make('habitaciones')
                                                    ->icon('heroicon-o-home')
                                                    ->placeholder('N/D'),
                                                TextEntry::make('banos')
                                                    ->label('Baños')
                                                    ->placeholder('N/D'),
                                                TextEntry::make('estacionamientos')
                                                    ->icon('heroicon-o-truck')
                                                    ->placeholder('N/D'),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        // TAB 2: COTIZACIÓN
                        // Tab::make('Cotización')
                        //     ->icon('heroicon-o-calculator')
                        //     ->badge(fn($record) => $record->precio_calculado ? '✓' : null)
                        //     ->badgeColor('success')
                        //     ->visible(fn($record) => $record->precio_calculado)
                        //     ->schema([
                        //         Section::make('📊 Desglose de Cotización')
                        //             ->schema([
                        //                 ViewEntry::make('desglose_cotizacion')
                        //                     ->label('')
                        //                     ->view('filament.infolists.desglose-cotizacion'),
                        //             ])
                        //             ->visible(
                        //                 function () {
                        //                     /** @var \App\Models\User $user */
                        //                     $user = Auth::user();

                        //                     return $user->can('propiedades_ver_desglose_cotizacion');
                        //                 }
                        //             ),

                        //         Section::make('💬 Retroalimentación de Aprobaciones')
                        //             ->description('Comentarios de Comercial y Contabilidad')
                        //             ->schema([
                        //                 ViewEntry::make('retroalimentacion')
                        //                     ->label('')
                        //                     ->view('filament.infolists.retroalimentacion-precio'),
                        //             ])
                        //             ->visible(fn($record) => $record->aprobacionesPrecio()->exists())
                        //             ->collapsible()
                        //             ->collapsed(fn($record) => $record->precio_aprobado),
                        //     ]),

                        // TAB 3: ADMIN Y LEGAL
                        Tab::make('Admin y Legal')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make('🏢 Datos Administrativos')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('sucursal.nombre')
                                                    ->label('Sucursal'),
                                                TextEntry::make('administradora.nombre')
                                                    ->label('Administradora')
                                                    ->visible(
                                                        function () {
                                                            /** @var \App\Models\User $user */
                                                            $user = Auth::user();

                                                            return $user->can('propiedades_ver_datos_sensibles');
                                                        }
                                                    ),
                                                TextEntry::make('cartera.nombre')
                                                    ->label('Cartera')
                                                    ->placeholder('Sin cartera'),
                                                TextEntry::make('nombre_acreditado')
                                                    ->label('Acreditado')
                                                    ->placeholder('No disponible')
                                                    ->visible(
                                                        function () {
                                                            /** @var \App\Models\User $user */
                                                            $user = Auth::user();

                                                            return $user->can('propiedades_ver_datos_sensibles');
                                                        }
                                                    ),
                                            ]),
                                    ]),

                                Section::make('⚖️ Información Jurídica')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('etapa_judicial_reportada')
                                                    ->label('Etapa Judicial')
                                                    ->placeholder('No especificada'),
                                                TextEntry::make('etapaProcesal.nombre')
                                                    ->label('Etapa Procesal (Cotización)')
                                                    ->badge()
                                                    ->color('info')
                                                    ->visible(fn($record) => $record->etapa_procesal_id),
                                                TextEntry::make('estatus_legal')
                                                    ->label('Estatus Legal')
                                                    ->badge()
                                                    ->color(fn($state) => match ($state) {
                                                        'R2_POSITIVO' => 'success',
                                                        'R1_NEGATIVO' => 'danger',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('fecha_corte_judicial')
                                                    ->label('Fecha Corte')
                                                    ->date()
                                                    ->placeholder('N/D'),
                                            ]),
                                    ]),
                            ]),

                        // TAB 4: GALERÍA
                        Tab::make('Galería')
                            ->icon('heroicon-o-photo')
                            ->badge(fn($record) => $record->archivos()->count())
                            ->visible(fn($record) => $record->archivos()->exists())
                            ->schema([
                                Section::make()
                                    ->schema([
                                        RepeatableEntry::make('archivos')
                                            ->label('')
                                            ->schema([
                                                ImageEntry::make('ruta_archivo')
                                                    ->label('') // Sin label
                                                    ->disk(config('filament.default_filesystem_disk', 'public'))
                                                    ->imageHeight(250) // ← Más alto
                                                    ->imageWidth('100%') // ← Ancho completo
                                                    ->extraImgAttributes([
                                                        'style' => 'object-fit: cover; border-radius: 8px;',
                                                    ])
                                                    ->columnSpanFull(),

                                                TextEntry::make('descripcion')
                                                    ->label('')
                                                    ->placeholder('Sin descripción')
                                                    ->color('gray')
                                                    ->size(TextSize::Small)
                                                    ->columnSpanFull(),

                                                TextEntry::make('tipo_archivo')
                                                    ->label('')
                                                    ->badge()
                                                    ->color('primary')
                                                    ->size(TextSize::Small)
                                                    ->visible(fn($state) => $state !== null),
                                            ])
                                            ->columns(1)
                                            ->grid([
                                                'default' => 1,  // Móvil: 1 columna
                                                'sm' => 2,       // Tablet: 2 columnas
                                                'lg' => 3,       // Desktop: 3 columnas
                                                'xl' => 4,       // Extra grande: 4 columnas
                                            ])
                                            ->contained(false), // ← Sin padding extra
                                    ])
                                    ->heading('') // ← Sin encabezado de sección
                                    ->description(''), // ← Sin descripción de sección
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
