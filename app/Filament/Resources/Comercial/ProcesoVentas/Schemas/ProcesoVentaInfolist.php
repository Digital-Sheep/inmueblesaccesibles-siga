<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Schemas;

use App\Models\ProcesoVenta;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;

class ProcesoVentaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ========================================
                // HEADER: INFORMACIÓN RÁPIDA
                // ========================================
                self::getHeaderSection(),

                // ========================================
                // TIMELINE VISUAL
                // ========================================
                self::getTimelineSection(),

                // ========================================
                // ACCIONES PRINCIPALES
                // ========================================
                self::getAccionesSection(),

                // ========================================
                // PESTAÑAS DE CONTENIDO
                // ========================================
                self::getTabs(),
            ]);
    }

    // ========================================
    // HEADER
    // ========================================

    private static function getHeaderSection(): Section
    {
        return Section::make()
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 5,
                ])->schema([
                    Grid::make(3)
                        ->columnSpan(3)
                        ->schema([
                            TextEntry::make('id')
                                ->label('Folio')
                                ->weight(FontWeight::Bold)
                                ->size('lg')
                                ->prefix('#'),

                            TextEntry::make('folio_apartado')
                                ->label('Folio Apartado')
                                ->placeholder('Pendiente')
                                ->weight(FontWeight::Bold),

                            TextEntry::make('estatus')
                                ->badge()
                                ->size('lg')
                                ->color(fn($state) => self::getEstatusColor($state)),
                        ]),

                    Grid::make(2)
                        ->columnSpan(2)
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Iniciado')
                                ->date('d/M/Y')
                                ->icon('heroicon-m-calendar'),

                            TextEntry::make('vendedor.name')
                                ->label('Asesor')
                                ->icon('heroicon-m-user')
                                ->weight(FontWeight::Bold),
                        ]),
                ]),
            ]);
    }

    // ========================================
    // TIMELINE VISUAL
    // ========================================

    private static function getTimelineSection(): Section
    {
        return Section::make('Progreso del Proceso')
            ->description('Seguimiento del estado actual')
            ->icon('heroicon-m-chart-bar')
            ->collapsed(false)
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                ])->schema([
                    // COLUMNA IZQUIERDA: Timeline visual
                    Group::make()
                        ->schema([
                            ViewEntry::make('timeline')
                                ->view('filament.infolists.proceso-venta-timeline-simple')
                                ->label(''),
                        ]),

                    // COLUMNA DERECHA: Detalles del estado actual
                    Group::make()
                        ->schema([
                            Section::make('Estado Actual')
                                ->schema([
                                    TextEntry::make('estatus')
                                        ->label('Fase')
                                        ->formatStateUsing(fn($state) => str_replace('_', ' ', $state))
                                        ->badge()
                                        ->color(fn($state) => self::getEstatusColor($state))
                                        ->size('lg'),

                                    TextEntry::make('descripcion_estado')
                                        ->label('Siguiente Paso')
                                        ->state(fn(ProcesoVenta $record) => self::getDescripcionEstado($record))
                                        ->icon('heroicon-m-arrow-right')
                                        ->iconColor('primary'),

                                    TextEntry::make('vendedor.name')
                                        ->label('Asesor Responsable')
                                        ->icon('heroicon-m-user')
                                        ->color('gray'),

                                    TextEntry::make('updated_at')
                                        ->label('Última Actualización')
                                        ->since()
                                        ->icon('heroicon-m-clock')
                                        ->color('gray'),
                                ]),
                        ]),
                ]),
            ]);
    }

    private static function getEtapaEntry(string $etapaKey, string $label, string $icon, string $emoji): TextEntry
    {
        return TextEntry::make('etapa_' . $etapaKey)
            ->label($emoji . ' ' . $label)
            ->state(fn(ProcesoVenta $record) => self::getEtapaTexto($record, $etapaKey))
            ->icon($icon)
            ->iconColor(fn(ProcesoVenta $record) => self::getEtapaColor($record, $etapaKey))
            ->color(fn(ProcesoVenta $record) => self::getEtapaColor($record, $etapaKey))
            ->weight(fn(ProcesoVenta $record) => self::getEtapaEstado($record, $etapaKey) === '→' ? FontWeight::Bold : FontWeight::Medium);
    }

    // ========================================
    // ACCIONES PRINCIPALES
    // ========================================

    private static function getAccionesSection(): Group
    {
        return Group::make()
            ->schema([
                Actions::make([
                    Action::make('accion_principal')
                        ->label(fn(ProcesoVenta $record) => self::getNextActionLabel($record))
                        ->icon(fn(ProcesoVenta $record) => self::getNextActionIcon($record))
                        ->color('primary')
                        ->button()
                        ->visible(fn(ProcesoVenta $record) => self::getNextActionLabel($record) !== null)
                        ->disabled()
                        ->extraAttributes(['class' => 'cursor-not-allowed opacity-75'])
                        ->tooltip('Regresa a la lista para ejecutar esta acción'),

                    Action::make('cancelar')
                        ->label('Cancelar proceso')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->outlined()
                        ->visible(
                            fn(ProcesoVenta $record) =>
                            $record->estatus !== 'CANCELADO' &&
                                $record->estatus !== 'ENTREGADO' &&
                                Auth::user()->can('ventas_cancelar')
                        )
                        ->disabled(),
                ])
                    ->alignCenter()
                    ->fullWidth(),
            ])
            ->visible(false);
    }

    // ========================================
    // PESTAÑAS
    // ========================================

    private static function getTabs(): Tabs
    {
        return Tabs::make('Detalles')
            ->tabs([
                self::getGeneralTab(),
                self::getClientePropiedadTab(),
                self::getPagosTab(),
                self::getDocumentosTab(),
                self::getJuridicoTab(),
                self::getHistorialTab(),
            ])
            ->columnSpanFull();
    }

    // ========================================
    // TAB: GENERAL
    // ========================================

    private static function getGeneralTab(): Tab
    {
        return Tab::make('General')
            ->icon('heroicon-m-information-circle')
            ->schema([
                Grid::make(3)->schema([
                    TextEntry::make('interesado.nombre_completo')
                        ->label('Cliente / Prospecto')
                        ->icon('heroicon-m-user')
                        ->weight(FontWeight::Bold)
                        ->columnSpan(2),

                    TextEntry::make('interesado_type')
                        ->label('Tipo')
                        ->formatStateUsing(
                            fn($state) =>
                            $state === 'App\\Models\\Prospecto' ? 'Prospecto' : 'Cliente'
                        )
                        ->badge()
                        ->color(
                            fn($state) =>
                            $state === 'App\\Models\\Prospecto' ? 'warning' : 'success'
                        ),
                ]),

                Grid::make(2)->schema([
                    TextEntry::make('fecha_visita')
                        ->label('Fecha de Visita')
                        ->date('d/M/Y H:i')
                        ->placeholder('No registrada')
                        ->icon('heroicon-m-home'),

                    TextEntry::make('resultado_visita')
                        ->label('Resultado Visita')
                        ->formatStateUsing(fn($state) => match ($state) {
                            'LE_GUSTO' => '✅ Le gustó',
                            'TIENE_DUDAS' => '🤔 Tiene dudas',
                            'NO_LE_GUSTO' => '❌ No le gustó',
                            default => 'N/A',
                        })
                        ->placeholder('N/A'),
                ]),

                TextEntry::make('observaciones_visita')
                    ->label('Observaciones de la Visita')
                    ->columnSpanFull()
                    ->placeholder('Sin observaciones'),

                Section::make('Montos')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('monto_enganche_solicitado')
                            ->label('Enganche Solicitado')
                            ->money('MXN')
                            ->placeholder('No solicitado'),

                        TextEntry::make('fecha_limite_enganche')
                            ->label('Fecha Límite Enganche')
                            ->date('d/M/Y')
                            ->placeholder('N/A'),

                        TextEntry::make('fecha_pago_enganche')
                            ->label('Pagado el')
                            ->date('d/M/Y')
                            ->placeholder('Pendiente')
                            ->icon('heroicon-m-check-circle')
                            ->iconColor('success'),

                        TextEntry::make('monto_liquidacion_solicitado')
                            ->label('Liquidación Solicitada')
                            ->money('MXN')
                            ->placeholder('No solicitado'),

                        TextEntry::make('fecha_limite_liquidacion')
                            ->label('Fecha Límite Liquidación')
                            ->date('d/M/Y')
                            ->placeholder('N/A'),

                        TextEntry::make('fecha_pago_liquidacion')
                            ->label('Liquidado el')
                            ->date('d/M/Y')
                            ->placeholder('Pendiente')
                            ->icon('heroicon-m-check-circle')
                            ->iconColor('success'),
                    ]),

                Section::make('Entrega')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('fecha_entrega_programada')
                            ->label('Entrega Programada')
                            ->date('d/M/Y H:i')
                            ->placeholder('No programada'),

                        TextEntry::make('fecha_entrega')
                            ->label('Entregado el')
                            ->date('d/M/Y H:i')
                            ->placeholder('Pendiente')
                            ->icon('heroicon-m-home-modern')
                            ->iconColor('success'),
                    ]),
            ]);
    }

    // ========================================
    // TAB: CLIENTE Y PROPIEDAD
    // ========================================

    private static function getClientePropiedadTab(): Tab
    {
        return Tab::make('Cliente y Propiedad')
            ->icon('heroicon-m-home-modern')
            ->schema([
                Section::make('Datos del Cliente')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('interesado.nombre_completo')
                            ->label('Nombre'),
                        TextEntry::make('interesado.email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('interesado.celular')
                            ->label('Celular')
                            ->icon('heroicon-m-phone'),
                        TextEntry::make('interesado.estatus')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'NUEVO' => 'info',
                                'CONTACTADO' => 'warning',
                                'CLIENTE' => 'success',
                                default => 'gray',
                            }),
                    ]),

                Section::make('Datos de la Propiedad')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('propiedad.numero_credito')
                            ->label('Número de Crédito')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('propiedad.estatus_comercial')
                            ->label('Estatus Comercial')
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'DISPONIBLE' => 'success',
                                'APARTADA' => 'warning',
                                'VENDIDA' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('propiedad.direccion_completa')
                            ->label('Dirección')
                            ->columnSpanFull()
                            ->icon('heroicon-m-map-pin'),
                        TextEntry::make('propiedad.precio_venta_sugerido')
                            ->label('Precio de Venta')
                            ->money('MXN')
                            ->weight(FontWeight::Bold),
                        // TextEntry::make('propiedad.administradora.nombre')
                        //     ->label('Administradora')
                        //     ->placeholder('N/A'),
                    ]),
            ]);
    }

    // ========================================
    // TAB: PAGOS
    // ========================================

    private static function getPagosTab(): Tab
    {
        return Tab::make('Pagos')
            ->icon('heroicon-m-banknotes')
            ->badge(fn(ProcesoVenta $record) => $record->pagos()->count())
            ->schema([
                RepeatableEntry::make('pagos')
                    ->label('Historial de Pagos')
                    ->schema([
                        Grid::make(5)->schema([
                            TextEntry::make('concepto')
                                ->badge()
                                ->color(fn($state) => match ($state) {
                                    'APARTADO' => 'info',
                                    'ENGANCHE' => 'warning',
                                    'LIQUIDACION' => 'success',
                                    default => 'gray',
                                }),
                            TextEntry::make('monto')
                                ->money('MXN')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('metodo_pago'),
                            TextEntry::make('estatus')
                                ->badge()
                                ->color(fn($state) => match ($state) {
                                    'PENDIENTE' => 'warning',
                                    'VALIDADO' => 'success',
                                    'RECHAZADO' => 'danger',
                                    default => 'gray',
                                }),
                            TextEntry::make('created_at')
                                ->label('Fecha')
                                ->date('d/M/Y'),
                        ]),
                    ])
                    ->placeholder('No hay pagos registrados')
                    ->contained(false),
            ]);
    }

    // ========================================
    // TAB: DOCUMENTOS
    // ========================================

    private static function getDocumentosTab(): Tab
    {
        return Tab::make('Documentos')
            ->icon('heroicon-m-document-text')
            ->badge(
                fn(ProcesoVenta $record) =>
                $record->archivos()->count() +
                    $record->interesado->archivos()->where('categoria', 'AVISO_PRIVACIDAD')->count()
            )
            ->schema([
                Section::make('Documentos del Proceso')
                    ->schema([
                        RepeatableEntry::make('archivos')
                            ->label('')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('categoria')
                                        ->badge()
                                        ->formatStateUsing(fn($state) => str_replace('_', ' ', $state)),
                                    TextEntry::make('nombre_original')
                                        ->label('Archivo'),
                                    TextEntry::make('created_at')
                                        ->label('Subido')
                                        ->date('d/M/Y H:i'),
                                ]),
                            ])
                            ->placeholder('No hay documentos cargados')
                            ->contained(false),
                    ]),

                Section::make('Documentos del Cliente')
                    ->schema([
                        RepeatableEntry::make('interesado.archivos')
                            ->label('')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('categoria')
                                        ->badge()
                                        ->formatStateUsing(fn($state) => str_replace('_', ' ', $state)),
                                    TextEntry::make('nombre_original')
                                        ->label('Archivo'),
                                    TextEntry::make('created_at')
                                        ->label('Subido')
                                        ->date('d/M/Y H:i'),
                                ]),
                            ])
                            ->placeholder('No hay documentos del cliente')
                            ->contained(false),
                    ]),
            ]);
    }

    // ========================================
    // TAB: JURÍDICO (solo lectura)
    // ========================================

    private static function getJuridicoTab(): Tab
    {
        return Tab::make('Jurídico')
            ->icon('heroicon-m-scale')
            ->visible(fn() => Auth::user()->can('ventas_ver_info_juridica'))
            ->schema([
                Section::make('Dictaminación')
                    ->description('Información de seguimiento jurídico')
                    ->schema([
                        RepeatableEntry::make('dictamenes')
                            ->label('Dictámenes')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('nomenclatura_generada')
                                        ->label('Nomenclatura')
                                        ->badge()
                                        ->color('info'),
                                    TextEntry::make('estatus')
                                        ->badge()
                                        ->color(fn($state) => match ($state) {
                                            'PENDIENTE' => 'warning',
                                            'TERMINADO' => 'success',
                                            'NEGATIVO' => 'danger',
                                            default => 'gray',
                                        }),
                                    TextEntry::make('usuario_solicitante.name')
                                        ->label('Solicitado por'),
                                    TextEntry::make('created_at')
                                        ->label('Fecha')
                                        ->date('d/M/Y'),
                                ]),
                            ])
                            ->placeholder('Sin dictámenes solicitados')
                            ->contained(false),
                    ]),

                Section::make('Proceso de Compra')
                    ->visible(fn(ProcesoVenta $record) => $record->procesoCompra !== null)
                    ->collapsed()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('procesoCompra.estatus')
                                ->label('Estatus de Compra')
                                ->badge(),
                            TextEntry::make('procesoCompra.precio_compra_negociado')
                                ->label('Precio Negociado')
                                ->money('MXN'),
                            TextEntry::make('procesoCompra.fecha_pago_proveedor')
                                ->label('Fecha de Pago')
                                ->date('d/M/Y'),
                        ]),
                    ]),
            ]);
    }

    // ========================================
    // TAB: HISTORIAL
    // ========================================

    private static function getHistorialTab(): Tab
    {
        return Tab::make('Historial')
            ->icon('heroicon-m-clock')
            ->schema([
                Section::make('Cambios de Estatus')
                    ->description('Log de cambios en el proceso')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Proceso iniciado')
                            ->dateTime('d/M/Y H:i')
                            ->icon('heroicon-m-play'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->since()
                            ->icon('heroicon-m-clock'),
                    ]),

                Section::make('Cancelación')
                    ->visible(fn(ProcesoVenta $record) => $record->estatus === 'CANCELADO')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('motivo_cancelacion')
                                ->label('Motivo')
                                ->formatStateUsing(fn($state) => str_replace('_', ' ', $state)),
                            TextEntry::make('fecha_cancelacion')
                                ->label('Fecha de Cancelación')
                                ->date('d/M/Y'),
                        ]),
                        TextEntry::make('detalles_cancelacion')
                            ->label('Detalles')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // ========================================
    // HELPERS
    // ========================================

    private static function getEstatusColor(string $estatus): string
    {
        return match ($estatus) {
            'ACTIVO', 'VISITA_REALIZADA' => 'info',
            'APARTADO_GENERADO', 'APARTADO_POR_VALIDAR' => 'warning',
            'APARTADO_VALIDADO', 'ENGANCHE_PAGADO', 'LIQUIDACION_PAGADA', 'ENTREGADO' => 'success',
            'EN_DICTAMINACION', 'EN_PROCESO_COMPRA' => 'primary',
            'CANCELADO' => 'danger',
            default => 'gray',
        };
    }

    private static function getNextActionLabel(ProcesoVenta $record): ?string
    {
        return match ($record->estatus) {
            'ACTIVO' => 'Continuar con siguiente paso',
            'VISITA_REALIZADA' => 'Generar contrato de apartado',
            'APARTADO_GENERADO' => 'Subir documentos',
            'APARTADO_VALIDADO' => 'Solicitar dictamen',
            'DICTAMINADO_POSITIVO' => 'Solicitar enganche',
            default => null,
        };
    }

    private static function getNextActionIcon(ProcesoVenta $record): string
    {
        return 'heroicon-m-arrow-right';
    }

    private static function getNextActionUrl(ProcesoVenta $record): ?string
    {
        // El action se ejecuta en la tabla, aquí solo mostramos info
        return null;
    }

    // ========================================
    // HELPERS TIMELINE
    // ========================================

    private static function getEtapaTexto(ProcesoVenta $record, string $etapaKey): string
    {
        $estado = self::getEtapaEstado($record, $etapaKey);

        return match ($estado) {
            '✓' => 'Completado',
            '→' => 'En proceso',
            default => 'Pendiente',
        };
    }

    private static function getEtapaEstado(ProcesoVenta $record, string $etapaKey): string
    {
        $estatusMap = [
            'ACTIVO' => 0,
            'VISITA_PROGRAMADA' => 0,
            'VISITA_REALIZADA' => 1,
            'APARTADO_GENERADO' => 2,
            'APARTADO_POR_VALIDAR' => 2,
            'APARTADO_VALIDADO' => 2,
            'EN_DICTAMINACION' => 3,
            'DICTAMINADO_POSITIVO' => 3,
            'ENGANCHE_SOLICITADO' => 4,
            'ENGANCHE_POR_VALIDAR' => 4,
            'ENGANCHE_PAGADO' => 4,
            'EN_PROCESO_COMPRA' => 5,
            'COMPRA_FINALIZADA' => 5,
            'LIQUIDACION_SOLICITADA' => 6,
            'LIQUIDACION_POR_VALIDAR' => 6,
            'LIQUIDACION_PAGADA' => 6,
            'EN_ESCRITURACION' => 7,
            'ESCRITURADO' => 7,
            'ENTREGA_PROGRAMADA' => 8,
            'ENTREGADO' => 8,
        ];

        $etapasMap = [
            'ACTIVO' => 0,
            'VISITA_REALIZADA' => 1,
            'APARTADO_VALIDADO' => 2,
            'DICTAMINADO_POSITIVO' => 3,
            'ENGANCHE_PAGADO' => 4,
            'COMPRA_FINALIZADA' => 5,
            'LIQUIDACION_PAGADA' => 6,
            'ESCRITURADO' => 7,
            'ENTREGADO' => 8,
        ];

        $etapaActual = $estatusMap[$record->estatus] ?? 0;
        $etapaBuscada = $etapasMap[$etapaKey] ?? 0;

        if ($etapaActual > $etapaBuscada) {
            return '✓';
        } elseif ($etapaActual === $etapaBuscada) {
            return '→';
        } else {
            return '○';
        }
    }

    private static function getEtapaColor(ProcesoVenta $record, string $etapaKey): string
    {
        if ($record->estatus === 'CANCELADO') {
            return 'gray';
        }

        $estado = self::getEtapaEstado($record, $etapaKey);

        return match ($estado) {
            '✓' => 'success',
            '→' => 'primary',
            default => 'gray',
        };
    }

    private static function getDescripcionEstado(ProcesoVenta $record): string
    {
        if ($record->estatus === 'CANCELADO') {
            return '❌ Proceso cancelado. Motivo: ' . ($record->motivo_cancelacion ? str_replace('_', ' ', $record->motivo_cancelacion) : 'No especificado');
        }

        return match ($record->estatus) {
            'ACTIVO' => '📝 En negociación inicial. Siguiente paso: Registrar visita a la propiedad.',
            'VISITA_REALIZADA' => '🏠 Visita realizada. Siguiente paso: Generar contrato de apartado.',
            'APARTADO_GENERADO' => '📄 Contrato generado. Esperando firma y pago del apartado.',
            'APARTADO_POR_VALIDAR' => '⏳ Pago de apartado en validación por Contabilidad.',
            'APARTADO_VALIDADO' => '✅ Apartado validado. Siguiente paso: Solicitar dictamen jurídico.',
            'EN_DICTAMINACION' => '⚖️ En proceso de dictaminación jurídica.',
            'DICTAMINADO_POSITIVO' => '✅ Dictamen aprobado. Siguiente paso: Solicitar enganche al cliente.',
            'ENGANCHE_SOLICITADO' => '💰 Enganche solicitado. Esperando pago del cliente.',
            'ENGANCHE_POR_VALIDAR' => '⏳ Pago de enganche en validación.',
            'ENGANCHE_PAGADO' => '✅ Enganche pagado. Proceso de compra iniciado.',
            'EN_PROCESO_COMPRA' => '🏢 En proceso de compra. GAD gestionando adquisición.',
            'COMPRA_FINALIZADA' => '✅ Propiedad adquirida. Siguiente paso: Solicitar liquidación.',
            'LIQUIDACION_SOLICITADA' => '💵 Liquidación solicitada. Esperando pago final.',
            'LIQUIDACION_POR_VALIDAR' => '⏳ Pago de liquidación en validación.',
            'LIQUIDACION_PAGADA' => '✅ Liquidación completada. Siguiente paso: Escrituración.',
            'EN_ESCRITURACION' => '📝 En proceso de escrituración.',
            'ESCRITURADO' => '✅ Escritura lista. Siguiente paso: Programar entrega.',
            'ENTREGA_PROGRAMADA' => '📅 Entrega programada para ' . ($record->fecha_entrega_programada?->format('d/m/Y') ?? 'fecha pendiente'),
            'ENTREGADO' => '🎉 ¡Proceso completado! Propiedad entregada exitosamente.',
            default => 'Proceso en curso.',
        };
    }
}
