<?php

namespace App\Filament\Resources\Comercial\Propiedades\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

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

                // ========================================
                // HEADER: Información Rápida
                // ========================================
                Section::make()
                    ->schema([
                        Grid::make(12)->schema([

                            // Columna izquierda: Datos principales
                            Group::make()
                                ->columnSpan(['default' => 12, 'md' => 8])
                                ->schema([
                                    TextEntry::make('numero_credito')
                                        ->label('No. Crédito / Folio')
                                        ->size(TextSize::Large)
                                        ->weight(FontWeight::Bold)
                                        ->copyable()
                                        ->icon('heroicon-o-identification')
                                        ->iconColor('primary'),

                                    TextEntry::make('direccion_completa')
                                        ->label('Dirección')
                                        ->icon('heroicon-o-map-pin')
                                        ->color('gray'),
                                ]),

                            // Columna derecha: Estatus
                            Group::make()
                                ->columnSpan(['default' => 12, 'md' => 4])
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextEntry::make('estatus_comercial')
                                            ->label('Estatus Comercial')
                                            ->badge()
                                            ->size(TextSize::Large)
                                            ->color(fn(string $state): string => match ($state) {
                                                'DISPONIBLE' => 'success',
                                                'APARTADA' => 'warning',
                                                'VENDIDA' => 'info',
                                                'BAJA' => 'danger',
                                                'BORRADOR' => 'gray',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('estatus_legal')
                                            ->label('Estatus Jurídico')
                                            ->badge()
                                            ->icon(fn(string $state): string => match ($state) {
                                                'R2_POSITIVO' => 'heroicon-o-check-circle',
                                                'R1_NEGATIVO' => 'heroicon-o-x-circle',
                                                'LITIGIO' => 'heroicon-o-scale',
                                                'ADJUDICADA' => 'heroicon-o-trophy',
                                                'ESCRITURADA' => 'heroicon-o-document-check',
                                                default => 'heroicon-o-question-mark-circle',
                                            })
                                            ->color(fn(string $state): string => match ($state) {
                                                'R2_POSITIVO', 'ADJUDICADA', 'ESCRITURADA' => 'success',
                                                'R1_NEGATIVO' => 'danger',
                                                'LITIGIO' => 'warning',
                                                default => 'gray',
                                            }),
                                    ]),
                                ]),
                        ]),
                    ])
                    ->columnSpanFull(),

                // ========================================
                // TABS PRINCIPALES
                // ========================================
                Tabs::make('Información Detallada')
                    ->contained(false) // ✨ Mejora visual
                    ->tabs([

                        // ========================================
                        // TAB 1: INFORMACIÓN GENERAL 🗺️
                        // ========================================
                        Tab::make('Información General')
                            ->icon('heroicon-o-map-pin')
                            ->schema([

                                // SECCIÓN: Asignación Administrativa
                                Section::make('📋 Asignación Administrativa')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextEntry::make('sucursal.nombre')
                                                ->label('Sucursal')
                                                ->icon('heroicon-o-building-office-2')
                                                ->color('primary')
                                                ->weight(FontWeight::Bold),

                                            TextEntry::make('cartera.nombre')
                                                ->label('Cartera')
                                                ->icon('heroicon-o-folder')
                                                ->placeholder('Sin cartera asignada')
                                                ->color('gray'),

                                            TextEntry::make('administradora.nombre')
                                                ->label('Administradora')
                                                ->icon('heroicon-o-building-library')
                                                ->placeholder('N/A')
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
                                    ->collapsed()
                                    ->columns(1),

                                // SECCIÓN: Ubicación
                                Section::make('📍 Ubicación del Inmueble')
                                    ->schema([
                                        // Fila 1: Estado, Municipio, CP
                                        Grid::make(3)->schema([
                                            TextEntry::make('estado.nombre')
                                                ->label('Estado')
                                                ->icon('heroicon-o-map')
                                                ->placeholder('N/D'),

                                            TextEntry::make('municipio.nombre')
                                                ->label('Municipio / Alcaldía')
                                                ->icon('heroicon-o-building-office-2')
                                                ->placeholder('N/D'),

                                            TextEntry::make('codigo_postal')
                                                ->label('Código Postal')
                                                ->icon('heroicon-o-envelope')
                                                ->placeholder('N/D'),
                                        ]),

                                        // Fila 2: Calle, Núm Ext, Núm Int
                                        Grid::make(3)->schema([
                                            TextEntry::make('calle')
                                                ->label('Calle')
                                                ->placeholder('N/D'),

                                            TextEntry::make('numero_exterior')
                                                ->label('Núm. Ext.')
                                                ->placeholder('N/A'),

                                            TextEntry::make('numero_interior')
                                                ->label('Núm. Int.')
                                                ->placeholder('N/A'),
                                        ]),

                                        // Fila 3: Colonia, Fraccionamiento (solo 2 columnas, está bien)
                                        Grid::make(3)->schema([
                                            TextEntry::make('colonia')
                                                ->label('Colonia')
                                                ->placeholder('N/D'),

                                            TextEntry::make('fraccionamiento')
                                                ->label('Fraccionamiento')
                                                ->placeholder('No especificado')
                                                ->columnSpan(2), // ← Ocupa 2 espacios para mantener alineación
                                        ]),

                                        // Fila 4: Google Maps (ocupa todo el ancho)
                                        TextEntry::make('google_maps_link')
                                            ->label('📍 Ver en Google Maps')
                                            ->url(fn($state) => $state)
                                            ->openUrlInNewTab()
                                            ->placeholder('No disponible')
                                            ->icon('heroicon-o-map-pin')
                                            ->color('primary')
                                            ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                                            ->columnSpanFull(),

                                        Grid::make(3)->schema([

                                            TextEntry::make('latitud')
                                                ->label('Latitud')
                                                ->placeholder('Sin coordenadas')
                                                ->icon('heroicon-o-arrows-up-down')
                                                ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 8) : null),

                                            TextEntry::make('longitud')
                                                ->label('Longitud')
                                                ->placeholder('Sin coordenadas')
                                                ->icon('heroicon-o-arrows-right-left')
                                                ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 8) : null),

                                            IconEntry::make('tiene_coordenadas')
                                                ->label('Geolocalizado')
                                                ->icon(fn($record) => $record->latitud && $record->longitud
                                                    ? 'heroicon-o-check-circle'
                                                    : 'heroicon-o-x-circle')
                                                ->color(fn($record) => $record->latitud && $record->longitud
                                                    ? 'success'
                                                    : 'danger')
                                                ->getStateUsing(fn($record) => (bool)($record->latitud && $record->longitud)),

                                        ])->columnSpanFull(),
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
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextEntry::make('tipo_inmueble')
                                                ->label('Tipo de Inmueble')
                                                ->badge()
                                                ->size(TextSize::Medium)
                                                ->color('primary')
                                                ->formatStateUsing(fn($state) => match ($state) {
                                                    'CASA' => '🏡 Casa',
                                                    'DEPARTAMENTO' => '🏢 Departamento',
                                                    'TERRENO' => '🌳 Terreno',
                                                    'LOCAL' => '🏪 Local Comercial',
                                                    default => $state,
                                                }),

                                            TextEntry::make('terreno_m2')
                                                ->label('Terreno')
                                                ->suffix(' m²')
                                                ->placeholder('N/D')
                                                ->icon('heroicon-o-square-3-stack-3d')
                                                ->weight(FontWeight::SemiBold),

                                            TextEntry::make('construccion_m2')
                                                ->label('Construcción')
                                                ->suffix(' m²')
                                                ->placeholder('N/D')
                                                ->icon('heroicon-o-building-office')
                                                ->weight(FontWeight::SemiBold),
                                        ]),

                                        Grid::make(3)->schema([
                                            TextEntry::make('habitaciones')
                                                ->label('🛏️ Habitaciones')
                                                ->placeholder('N/D')
                                                ->size(TextSize::Medium),

                                            TextEntry::make('banos')
                                                ->label('🚿 Baños')
                                                ->placeholder('N/D')
                                                ->size(TextSize::Medium),

                                            TextEntry::make('estacionamientos')
                                                ->label('🚗 Estacionamientos')
                                                ->placeholder('N/D')
                                                ->size(TextSize::Medium),
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
                                // SECCIÓN: Precios Base
                                Section::make('💰 Precios Base')
                                    ->description('Valores de referencia de la propiedad')
                                    ->schema([
                                        // Fila 1: Los 2 más importantes (DESTACADOS)
                                        Grid::make(2)->schema([
                                            TextEntry::make('precio_lista')
                                                ->label('Precio de Lista')
                                                ->money('MXN')
                                                ->size(TextSize::Large)
                                                ->weight(FontWeight::Bold)
                                                ->color('primary')
                                                ->placeholder('N/D')
                                                ->helperText('Precio en la administradora')
                                                ->visible(
                                                    function () {
                                                        /** @var \App\Models\User $user */
                                                        $user = Auth::user();
                                                        return $user && $user->can('propiedades_ver_datos_sensibles');
                                                    }
                                                ),

                                            TextEntry::make('precio_valor_comercial')
                                                ->label('Valor Comercial')
                                                ->money('MXN')
                                                ->size(TextSize::Large)
                                                ->weight(FontWeight::Bold)
                                                ->color('success')
                                                ->placeholder('N/D')
                                                ->helperText('Valor de mercado actual'),
                                        ]),

                                        // Fila 2: Datos complementarios
                                        Grid::make(2)->schema([
                                            TextEntry::make('avaluo_banco')
                                                ->label('Avalúo del Banco')
                                                ->money('MXN')
                                                ->placeholder('No disponible')
                                                ->color('gray'),

                                            TextEntry::make('cofinavit_monto')
                                                ->label('COFINAVIT')
                                                ->money('MXN')
                                                ->placeholder('N/A')
                                                ->color('info'),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->columns(1),

                                // SECCIÓN: Precios Calculados (si existen)
                                Section::make('🧮 Precios')
                                    ->description('Datos de la cotización activa del sistema')
                                    ->visible(
                                        function ($record) {
                                            return $record->cotizaciones()->where('activa', true)->exists();
                                        }
                                    )
                                    ->schema([
                                        // Mostrar todos los campos de la cotización activa
                                        Grid::make(2)->schema([
                                            // PRECIOS PRINCIPALES
                                            TextEntry::make('id')
                                                ->label('Precio de Venta Sugerido')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion ? '$' . number_format($cotizacion->precio_venta_sugerido, 2) : 'N/A';
                                                })
                                                ->size(TextSize::Large)
                                                ->weight(FontWeight::Bold)
                                                ->color('success')
                                                ->helperText('Precio antes de descuento'),

                                            TextEntry::make('id')
                                                ->label('Precio con Descuento')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion ? '$' . number_format($cotizacion->precio_venta_con_descuento, 2) : 'N/A';
                                                })
                                                ->size(TextSize::Large)
                                                ->weight(FontWeight::Bold)
                                                ->color('primary')
                                                ->helperText(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    if (!$cotizacion || !$cotizacion->porcentaje_descuento) return 'Precio final de venta';
                                                    return 'Con ' . number_format($cotizacion->porcentaje_descuento, 1) . '% de descuento';
                                                }),
                                        ]),

                                        Grid::make(2)->schema([
                                            TextEntry::make('id')
                                                ->label('Precio Sin Remodelación')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion ? '$' . number_format($cotizacion->precio_sin_remodelacion, 2) : 'N/A';
                                                })
                                                ->color('gray')
                                                ->helperText('Base sin mejoras'),

                                            TextEntry::make('id')
                                                ->label('Precio Base')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion ? '$' . number_format($cotizacion->precio_base, 2) : 'N/A';
                                                })
                                                ->color('gray')
                                                ->helperText('Precio lista usado en cálculo')
                                                ->visible(
                                                    function () {
                                                        /** @var \App\Models\User $user */
                                                        $user = Auth::user();
                                                        return $user && $user->can('propiedades_ver_datos_sensibles');
                                                    }
                                                )
                                        ]),

                                        // COSTOS Y GASTOS
                                        Section::make('💵 Desglose de Costos')
                                            ->description('Costos estimados para la inversión')
                                            ->schema([
                                                Grid::make(3)->schema([
                                                    TextEntry::make('id')
                                                        ->label('🔨 Remodelación')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? '$' . number_format($cotizacion->costo_remodelacion, 2) : 'N/A';
                                                        }),

                                                    TextEntry::make('id')
                                                        ->label('💡 Luz')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? '$' . number_format($cotizacion->costo_luz, 2) : 'N/A';
                                                        }),

                                                    TextEntry::make('id')
                                                        ->label('💧 Agua')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? '$' . number_format($cotizacion->costo_agua, 2) : 'N/A';
                                                        }),
                                                ]),

                                                Grid::make(3)->schema([
                                                    TextEntry::make('id')
                                                        ->label('🏛️ Predial')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? '$' . number_format($cotizacion->costo_predial, 2) : 'N/A';
                                                        }),

                                                    TextEntry::make('id')
                                                        ->label('⚖️ Gastos Jurídicos')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? '$' . number_format($cotizacion->costo_gastos_juridicos, 2) : 'N/A';
                                                        }),

                                                    TextEntry::make('id')
                                                        ->label('💰 Total Costos')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? '$' . number_format($cotizacion->total_costos, 2) : 'N/A';
                                                        })
                                                        ->weight(FontWeight::Bold)
                                                        ->color('warning'),
                                                ]),
                                            ])
                                            ->collapsible()
                                            ->collapsed()
                                            ->visible(
                                                function () {
                                                    /** @var \App\Models\User $user */
                                                    $user = Auth::user();
                                                    return $user && $user->can('propiedades_ver_desglose_cotizacion');
                                                }
                                            ),

                                        // MÁRGENES Y RENTABILIDAD
                                        Section::make('📈 Análisis de Rentabilidad')
                                            ->description('Porcentajes de inversión y utilidad')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    TextEntry::make('id')
                                                        ->label('📊 % Inversión')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? number_format($cotizacion->porcentaje_inversion, 2) . '%' : 'N/A';
                                                        })
                                                        ->badge()
                                                        ->color('info'),

                                                    TextEntry::make('id')
                                                        ->label('💹 % Utilidad (Precio con descuento)')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? number_format($cotizacion->porcentaje_utilidad, 2) . '%' : 'N/A';
                                                        })
                                                        ->badge()
                                                        ->color('success'),
                                                ]),

                                                Grid::make(2)->schema([
                                                    TextEntry::make('id')
                                                        ->label('🎯 % Descuento')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            if (!$cotizacion || !$cotizacion->porcentaje_descuento) return 'Sin descuento';
                                                            return number_format($cotizacion->porcentaje_descuento, 2) . '%';
                                                        })
                                                        ->badge()
                                                        ->color('primary'),

                                                    TextEntry::make('id')
                                                        ->label('💵 Monto de Inversión Total')
                                                        ->formatStateUsing(function ($record) {
                                                            $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                            return $cotizacion ? '$' . number_format($cotizacion->monto_inversion, 2) : 'N/A';
                                                        })
                                                        ->size(TextSize::Medium)
                                                        ->weight(FontWeight::Bold)
                                                        ->color('warning')

                                                ]),
                                            ])
                                            ->collapsible()
                                            ->collapsed()
                                            ->visible(
                                                function ($record) {
                                                    /** @var \App\Models\User $user */
                                                    $user = Auth::user();

                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();


                                                    return $user && $user->can('propiedades_ver_utilidad');
                                                }
                                            ),

                                        // METADATOS
                                        Grid::make(4)->schema([
                                            TextEntry::make('id')
                                                ->label('📋 Versión')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion ? 'v' . $cotizacion->version : 'N/A';
                                                })
                                                ->badge(),

                                            TextEntry::make('id')
                                                ->label('📦 Tamaño')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion ? $cotizacion->tamano_propiedad : 'N/A';
                                                }),

                                            TextEntry::make('id')
                                                ->label('⚖️ Etapa Procesal')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion && $cotizacion->etapaProcesal
                                                        ? $cotizacion->etapaProcesal->nombre
                                                        : 'N/A';
                                                }),

                                            TextEntry::make('id')
                                                ->label('👤 Calculado Por')
                                                ->formatStateUsing(function ($record) {
                                                    $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                    return $cotizacion && $cotizacion->calculadaPor
                                                        ? $cotizacion->calculadaPor->name
                                                        : 'N/A';
                                                })
                                                ->icon('heroicon-o-user'),
                                        ])
                                            ->visible(
                                                function ($record) {
                                                    /** @var \App\Models\User $user */
                                                    $user = Auth::user();

                                                    return $user && $user->can('propiedades_ver_datos_sensibles');
                                                }
                                            ),

                                        // NOTAS (si existen)
                                        TextEntry::make('id')
                                            ->label('📝 Notas de Cotización')
                                            ->formatStateUsing(function ($record) {
                                                $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                return $cotizacion && $cotizacion->notas ? $cotizacion->notas : 'Sin notas';
                                            })
                                            ->columnSpanFull()
                                            ->visible(function ($record) {
                                                /** @var \App\Models\User $user */
                                                $user = Auth::user();

                                                $cotizacion = $record->cotizaciones()->where('activa', true)->first();
                                                return $user && $user->can('propiedades_ver_datos_sensibles') && $cotizacion && $cotizacion->notas;
                                            }),
                                    ])
                                    ->collapsible()
                                    ->collapsed()
                                    ->columns(1),

                                // SECCIÓN: Estado de Cotización
                                Section::make('📊 Estado de Cotización')
                                    ->visible(
                                        function ($record) {
                                            /** @var \App\Models\User $user */
                                            $user = Auth::user();
                                            return $user && $user->can('propiedades_ver_datos_sensibles') && $record->precio_calculado;
                                        }
                                    )
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextEntry::make('precio_calculado')
                                                ->label('Precio Calculado')
                                                ->badge()
                                                ->formatStateUsing(fn($state) => $state ? '✅ Sí' : '⏳ No')
                                                ->color(fn($state) => $state ? 'success' : 'gray'),

                                            TextEntry::make('precio_aprobado')
                                                ->label('Precio Aprobado')
                                                ->badge()
                                                ->formatStateUsing(fn($state) => $state ? '✅ Aprobado' : '⏳ Pendiente')
                                                ->color(fn($state) => $state ? 'success' : 'warning'),

                                            TextEntry::make('precio_requiere_decision_dge')
                                                ->label('Requiere DGE')
                                                ->badge()
                                                ->formatStateUsing(fn($state) => $state ? '⚠️ Sí' : '✅ No')
                                                ->color(fn($state) => $state ? 'danger' : 'success'),
                                        ]),

                                        // Estado de Aprobaciones por Área
                                        Grid::make(2)->schema([
                                            TextEntry::make('id')  // ← Usar campo real como base
                                                ->label('🏪 Aprobación Comercial')
                                                ->formatStateUsing(function ($record) {
                                                    $aprobacion = $record->aprobacionesPrecio()
                                                        ->where('tipo_aprobador', 'COMERCIAL')
                                                        ->latest()
                                                        ->first();

                                                    if (!$aprobacion || $aprobacion->estatus == 'PENDIENTE') {
                                                        return '⏳ Pendiente de revisión';
                                                    }

                                                    return $aprobacion->estatus == 'APROBADO'
                                                        ? '✅ Aprobado'
                                                        : '❌ Rechazado';
                                                })
                                                ->badge()
                                                ->color(function ($record) {
                                                    $aprobacion = $record->aprobacionesPrecio()
                                                        ->where('tipo_aprobador', 'COMERCIAL')
                                                        ->latest()
                                                        ->first();

                                                    if (!$aprobacion) return 'warning';
                                                    return $aprobacion->aprobado ? 'success' : 'danger';
                                                }),

                                            TextEntry::make('id')  // ← Usar campo real como base
                                                ->label('💰 Aprobación Contabilidad')
                                                ->formatStateUsing(function ($record) {
                                                    $aprobacion = $record->aprobacionesPrecio()
                                                        ->where('tipo_aprobador', 'CONTABILIDAD')
                                                        ->latest()
                                                        ->first();

                                                    if (!$aprobacion || $aprobacion->estatus == 'PENDIENTE') {
                                                        return '⏳ Pendiente de revisión';
                                                    }

                                                    return $aprobacion->aprobado
                                                        ? '✅ Aprobado'
                                                        : '❌ Rechazado';
                                                })
                                                ->badge()
                                                ->color(function ($record) {
                                                    $aprobacion = $record->aprobacionesPrecio()
                                                        ->where('tipo_aprobador', 'CONTABILIDAD')
                                                        ->latest()
                                                        ->first();

                                                    if (!$aprobacion) return 'warning';
                                                    return $aprobacion->aprobado ? 'success' : 'danger';
                                                }),
                                        ]),

                                        // Comentarios de las Aprobaciones
                                        RepeatableEntry::make('aprobacionesPrecio')
                                            ->label('💬 Comentarios de Revisión')
                                            ->schema([
                                                Grid::make(4)->schema([
                                                    TextEntry::make('tipo_aprobador')
                                                        ->badge()
                                                        ->color(fn($state) => match ($state) {
                                                            'COMERCIAL' => 'info',
                                                            'CONTABILIDAD' => 'warning',
                                                            default => 'gray',
                                                        })
                                                        ->formatStateUsing(fn($state) => match ($state) {
                                                            'COMERCIAL' => '🏪 Comercial',
                                                            'CONTABILIDAD' => '💰 Contabilidad',
                                                            default => $state,
                                                        }),

                                                    TextEntry::make('estatus')
                                                        ->label('Decisión')
                                                        ->badge()
                                                        ->formatStateUsing(fn($state) => $state == 'APROBADO' ? '✅ Aprobado' : '❌ Rechazado')
                                                        ->color(fn($state) => $state ? 'success' : 'danger'),

                                                    TextEntry::make('aprobador.name')
                                                        ->label('Revisado por')
                                                        ->icon('heroicon-o-user'),

                                                    TextEntry::make('updated_at')
                                                        ->label('Fecha')
                                                        ->date('d/M/Y H:i')
                                                        ->icon('heroicon-o-calendar'),
                                                ]),

                                                TextEntry::make('comentarios')
                                                    ->label('Comentario')
                                                    ->placeholder('Sin comentarios')
                                                    ->columnSpanFull()
                                                    ->color('gray'),
                                            ])
                                            ->columns(1)
                                            ->contained(false)
                                            ->visible(fn($record) => $record->aprobacionesPrecio()->exists() && $record->aprobacionesPrecio()->whereNotNull('comentarios')->exists()),
                                    ])
                                    ->collapsible()
                                    ->columns(1),

                                // SECCIÓN: Historial de Cotizaciones
                                Section::make('📜 Historial de Cotizaciones')
                                    ->visible(
                                        function ($record) {
                                            /** @var \App\Models\User $user */
                                            $user = Auth::user();
                                            return $user && $user->can('propiedades_ver_datos_sensibles') && $record->cotizaciones()->exists();
                                        }
                                    )
                                    ->schema([
                                        RepeatableEntry::make('cotizaciones')
                                            ->label('')
                                            ->schema([
                                                Grid::make(6)->schema([
                                                    TextEntry::make('version')
                                                        ->label('Versión')
                                                        ->badge()
                                                        ->color('primary')
                                                        ->formatStateUsing(fn($state) => "v{$state}"),

                                                    TextEntry::make('activa')
                                                        ->label('Estado')
                                                        ->badge()
                                                        ->formatStateUsing(fn($state) => $state ? '✅ Activa' : '📋 Histórica')
                                                        ->color(fn($state) => $state ? 'success' : 'gray'),

                                                    TextEntry::make('precio_venta_sugerido')
                                                        ->label('Precio Sugerido')
                                                        ->money('MXN')
                                                        ->weight(FontWeight::Bold)
                                                        ->color('success'),

                                                    TextEntry::make('porcentaje_descuento')
                                                        ->label('Descuento')
                                                        ->suffix('%')
                                                        ->color('warning'),

                                                    TextEntry::make('porcentaje_utilidad')
                                                        ->label('Utilidad')
                                                        ->suffix('%')
                                                        ->color('info'),

                                                    TextEntry::make('created_at')
                                                        ->label('Calculada')
                                                        ->date('d/M/Y')
                                                        ->icon('heroicon-o-calendar')
                                                        ->color('gray'),
                                                ]),

                                                TextEntry::make('notas')
                                                    ->label('Notas')
                                                    ->placeholder('Sin notas')
                                                    ->columnSpanFull()
                                                    ->color('gray')
                                                    ->size(TextSize::Small)
                                                    ->visible(fn($state) => $state !== null),
                                            ])
                                            ->columns(1)
                                            ->contained(false),
                                    ])
                                    ->collapsible()
                                    ->collapsed()
                                    ->columns(1),

                                // SECCIÓN: Información Legal
                                Section::make('⚖️ Información Legal')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('etapa_judicial_reportada')
                                                ->label('Etapa Judicial')
                                                ->icon('heroicon-o-scale')
                                                ->placeholder('N/D')
                                                ->color('primary'),

                                            TextEntry::make('segunda_etapa')
                                                ->label('Segunda Etapa')
                                                ->placeholder('N/D')
                                                ->color('gray'),

                                            TextEntry::make('fecha_corte_judicial')
                                                ->label('Fecha de Corte')
                                                ->date('d/M/Y')
                                                ->icon('heroicon-o-calendar')
                                                ->placeholder('N/D'),

                                            TextEntry::make('nombre_acreditado')
                                                ->label('Acreditado / Deudor')
                                                ->icon('heroicon-o-user')
                                                ->placeholder('Confidencial')
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
                            ->badge(fn($record) => $record->archivos()->count())
                            ->badgeColor('primary')
                            ->visible(fn($record) => $record->archivos()->exists())
                            ->schema([
                                Section::make()
                                    ->schema([
                                        RepeatableEntry::make('archivos')
                                            ->label('')
                                            ->schema([
                                                ImageEntry::make('ruta_archivo')
                                                    ->label('')
                                                    ->disk('public')
                                                    ->height(280)
                                                    ->width('100%')
                                                    ->extraImgAttributes([
                                                        'style' => 'object-fit: cover; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);',
                                                    ])
                                                    ->columnSpanFull(),

                                                Grid::make(2)->schema([
                                                    TextEntry::make('categoria')
                                                        ->badge()
                                                        ->size(TextSize::Medium)
                                                        ->color('primary')
                                                        ->formatStateUsing(fn($state) => match ($state) {
                                                            'FACHADA' => '🏠 Fachada',
                                                            'INTERIOR' => '🛋️ Interior',
                                                            'PATIO' => '🌳 Patio/Jardín',
                                                            'PLANO' => '🗺️ Plano',
                                                            'DAMAGE' => '⚠️ Daños',
                                                            'LEGAL' => '⚖️ Legal',
                                                            default => $state,
                                                        }),

                                                    TextEntry::make('created_at')
                                                        ->label('Subido el')
                                                        ->date('d/M/Y')
                                                        ->icon('heroicon-o-calendar')
                                                        ->color('gray'),
                                                ]),

                                                TextEntry::make('descripcion')
                                                    ->label('')
                                                    ->placeholder('Sin descripción')
                                                    ->color('gray')
                                                    ->size(TextSize::Small)
                                                    ->columnSpanFull()
                                                    ->visible(fn($state) => $state !== null),
                                            ])
                                            ->columns(1)
                                            ->grid([
                                                'default' => 1,
                                                'sm' => 2,
                                                'lg' => 3,
                                                'xl' => 4,
                                            ])
                                            ->contained(false),
                                    ])
                                    ->heading('')
                                    ->description(''),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
