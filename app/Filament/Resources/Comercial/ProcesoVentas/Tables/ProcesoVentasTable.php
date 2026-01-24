<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Tables;

use App\Models\Dictamen;
use App\Models\Propiedad;
use App\Models\ProcesoVenta;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\RawJs;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProcesoVentasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->recordAction(ViewAction::class)
            ->recordActions(self::getActions());
    }

    // ========================================
    // COLUMNAS
    // ========================================

    private static function getColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('Folio')
                ->sortable()
                ->searchable(),

            TextColumn::make('interesado_id')
                ->label('Cliente / Prospecto')
                ->formatStateUsing(function (ProcesoVenta $record) {
                    if ($record->interesado_type === 'App\\Models\\Prospecto') {
                        return $record->interesado->nombre_completo . " (Prospecto)";
                    }

                    return $record->interesado->nombres . " " . $record->interesado->apellido_paterno . " (Cliente)";
                })
                ->icon('heroicon-m-user')
                ->weight('bold'),

            TextColumn::make('propiedad.numero_credito')
                ->label('Propiedad')
                ->description(fn(ProcesoVenta $record) => Str::limit($record->propiedad->direccion_completa, 40))
                ->searchable(),

            TextColumn::make('estatus')
                ->badge()
                ->color(fn(string $state): string => self::getEstatusColor($state)),

            TextColumn::make('vendedor.name')
                ->label('Vendedor')
                ->toggleable(),

            TextColumn::make('created_at')
                ->date('d/M/Y')
                ->label('Iniciado')
                ->sortable(),
        ];
    }

    // ========================================
    // FILTROS
    // ========================================

    private static function getFilters(): array
    {
        return [
            SelectFilter::make('estatus')
                ->options([
                    'ACTIVO' => 'En Negociación',
                    'APARTADO_VALIDADO' => 'Apartados',
                    'ENGANCHE_PAGADO' => 'Con Enganche',
                    'CANCELADO' => 'Cancelados',
                ]),

            SelectFilter::make('vendedor_id')
                ->relationship('vendedor', 'name')
                ->label('Vendedor'),
        ];
    }

    // ========================================
    // ACCIONES
    // ========================================

    private static function getActions(): array
    {
        return [
            // Action principal dinámico
            Action::make('siguiente_paso')
                ->label(fn(ProcesoVenta $record) => self::getNextActionLabel($record))
                ->icon(fn(ProcesoVenta $record) => self::getNextActionIcon($record))
                ->color(fn(ProcesoVenta $record) => self::getNextActionColor($record))
                ->button()
                ->visible(fn(ProcesoVenta $record) => self::hasNextAction($record))
                ->schema(fn(ProcesoVenta $record) => self::getNextActionSchema($record))
                ->action(fn(ProcesoVenta $record, array $data) => self::executeNextAction($record, $data))
                ->extraAttributes(fn(ProcesoVenta $record) => self::getNextActionExtraAttributes($record)),

            // Ver siempre visible
            ViewAction::make(),

            // Cancelar (solo para gerentes+)
            Action::make('cancelar')
                ->label('Cancelar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(
                    function (ProcesoVenta $record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $record->estatus !== 'CANCELADO' &&
                            $record->estatus !== 'ENTREGADO' &&
                            $user->can('ventas_cancelar');
                    }
                )
                ->schema(self::getCancelarSchema())
                ->action(fn(ProcesoVenta $record, array $data) => self::cancelarProceso($record, $data)),
        ];
    }

    // ========================================
    // HELPERS: CONFIGURACIÓN DE SIGUIENTE PASO
    // ========================================

    private static function hasNextAction(ProcesoVenta $record): bool
    {
        return self::getNextActionLabel($record) !== null;
    }

    private static function getNextActionLabel(ProcesoVenta $record): ?string
    {
        return match ($record->estatus) {
            'ACTIVO' => !$record->interesado->archivos()->where('categoria', 'AVISO_PRIVACIDAD')->exists()
                ? 'Subir Aviso de Privacidad'
                : 'Registrar Visita',
            'VISITA_REALIZADA' => 'Generar Contrato de Apartado',
            'APARTADO_GENERADO' => !$record->archivos()->where('categoria', 'CONTRATO_APARTADO_FIRMADO')->exists()
                ? 'Subir Contrato Firmado'
                : 'Subir Pago de Apartado',
            'APARTADO_VALIDADO' => 'Solicitar Dictamen',
            'DICTAMINADO_POSITIVO' => 'Solicitar Enganche',
            'ENGANCHE_SOLICITADO' => 'Subir Pago de Enganche',
            'COMPRA_FINALIZADA' => 'Solicitar Liquidación',
            'LIQUIDACION_SOLICITADA' => 'Subir Pago de Liquidación',
            'ESCRITURADO' => 'Programar Entrega',
            'ENTREGA_PROGRAMADA' => 'Registrar Entrega Final',
            default => null,
        };
    }

    private static function getNextActionIcon(ProcesoVenta $record): string
    {
        return match ($record->estatus) {
            'ACTIVO' => !$record->interesado->archivos()->where('categoria', 'AVISO_PRIVACIDAD')->exists()
                ? 'heroicon-o-shield-check'
                : 'heroicon-o-home',
            'VISITA_REALIZADA' => 'heroicon-o-document-text',
            'APARTADO_GENERADO' => 'heroicon-o-document-arrow-up',
            'APARTADO_VALIDADO' => 'heroicon-o-scale',
            'DICTAMINADO_POSITIVO', 'COMPRA_FINALIZADA', 'LIQUIDACION_SOLICITADA' => 'heroicon-o-currency-dollar',
            'ENGANCHE_SOLICITADO' => 'heroicon-o-arrow-up-tray',
            'ESCRITURADO' => 'heroicon-o-calendar',
            'ENTREGA_PROGRAMADA' => 'heroicon-o-home-modern',
            default => 'heroicon-o-arrow-right',
        };
    }

    private static function getNextActionColor(ProcesoVenta $record): string
    {
        return match ($record->estatus) {
            'DICTAMINADO_POSITIVO', 'ENGANCHE_SOLICITADO', 'LIQUIDACION_SOLICITADA' => 'success',
            'APARTADO_VALIDADO' => 'info',
            'ESCRITURADO', 'ENTREGA_PROGRAMADA' => 'warning',
            default => 'primary',
        };
    }

    private static function getNextActionExtraAttributes(ProcesoVenta $record): array
    {
        // Para generar contrato (abre en nueva pestaña)
        if ($record->estatus === 'VISITA_REALIZADA') {
            return [
                'onclick' => "window.open('" . route('generar.contrato.apartado', $record) . "', '_blank')"
            ];
        }

        return [];
    }

    // ========================================
    // SCHEMAS POR TIPO DE ACCIÓN
    // ========================================

    private static function getNextActionSchema(ProcesoVenta $record): array
    {
        return match ($record->estatus) {
            'ACTIVO' => !$record->interesado->archivos()->where('categoria', 'AVISO_PRIVACIDAD')->exists()
                ? self::getSubirAvisoSchema()
                : self::getRegistrarVisitaSchema(),
            'APARTADO_GENERADO' => !$record->archivos()->where('categoria', 'CONTRATO_APARTADO_FIRMADO')->exists()
                ? self::getSubirContratoFirmadoSchema()
                : self::getSubirPagoApartadoSchema(),
            'APARTADO_VALIDADO' => self::getSolicitarDictamenSchema(),
            'DICTAMINADO_POSITIVO' => self::getSolicitarEngancheSchema(),
            'ENGANCHE_SOLICITADO' => self::getSubirPagoEngancheSchema(),
            'COMPRA_FINALIZADA' => self::getSolicitarLiquidacionSchema(),
            'LIQUIDACION_SOLICITADA' => self::getSubirPagoLiquidacionSchema(),
            'ESCRITURADO' => self::getProgramarEntregaSchema(),
            'ENTREGA_PROGRAMADA' => self::getRegistrarEntregaFinalSchema(),
            default => [],
        };
    }

    // ========================================
    // ACCIÓN 1: SUBIR AVISO DE PRIVACIDAD
    // ========================================

    private static function getSubirAvisoSchema(): array
    {
        return [
            FileUpload::make('archivo_temporal')
                ->label('Documento Firmado')
                ->disk('public')
                ->directory('legal/avisos')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->maxSize(10240)
                ->required(),
        ];
    }

    // ========================================
    // ACCIÓN 2: REGISTRAR VISITA
    // ========================================

    private static function getRegistrarVisitaSchema(): array
    {
        return [
            DateTimePicker::make('fecha_visita')
                ->label('Fecha y hora de la visita')
                ->required()
                ->maxDate(now())
                ->native(false),

            Select::make('resultado_visita')
                ->label('Resultado de la visita')
                ->options([
                    'LE_GUSTO' => '✅ Le gustó la propiedad',
                    'TIENE_DUDAS' => '🤔 Tiene dudas',
                    'NO_LE_GUSTO' => '❌ No le gustó',
                ])
                ->required()
                ->native(false),

            Textarea::make('observaciones_visita')
                ->label('Observaciones de la visita')
                ->rows(3)
                ->placeholder('Comentarios sobre la visita, reacciones del cliente, etc.'),
        ];
    }

    // ========================================
    // ACCIÓN 3: SUBIR CONTRATO FIRMADO
    // ========================================

    private static function getSubirContratoFirmadoSchema(): array
    {
        return [
            FileUpload::make('archivo_temporal')
                ->label('Contrato de Apartado Firmado')
                ->disk('public')
                ->directory('contratos/apartado')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(10240)
                ->required()
                ->helperText('Contrato firmado por el cliente'),
        ];
    }

    // ========================================
    // ACCIÓN 4: SUBIR PAGO DE APARTADO
    // ========================================

    private static function getSubirPagoApartadoSchema(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('monto')
                    ->label('Monto del apartado')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->default(10000)
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),

                Select::make('metodo_pago')
                    ->label('Método de pago')
                    ->options([
                        'TRANSFERENCIA' => 'Transferencia',
                        'EFECTIVO' => 'Efectivo',
                        'TARJETA' => 'Tarjeta',
                    ])
                    ->required()
                    ->native(false),
            ]),

            FileUpload::make('comprobante_url')
                ->label('Comprobante de pago')
                ->disk('public')
                ->directory('pagos')
                ->required(),
        ];
    }

    // ========================================
    // ACCIÓN 5: SOLICITAR DICTAMEN
    // ========================================

    private static function getSolicitarDictamenSchema(): array
    {
        return [
            Section::make('Información para jurídico')
                ->description('Captura los datos preliminares requeridos para iniciar la investigación.')
                ->schema([
                    TextInput::make('nombre_proveedor')
                        ->label('Nombre del proveedor / Dueño')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('numero_credito')
                        ->label('Número de crédito (Si aplica)'),

                    Grid::make(2)->schema([
                        Toggle::make('es_dueno_real')
                            ->label('¿El proveedor es el dueño real?')
                            ->required(),

                        Toggle::make('tiene_posesion')
                            ->label('¿Tiene posesión física?')
                            ->required(),
                    ]),

                    DatePicker::make('fecha_ultimo_pago_deudor')
                        ->label('Fecha último pago (Si se conoce)')
                        ->native(false),

                    Textarea::make('observaciones')
                        ->label('Notas para jurídico')
                        ->rows(3),
                ]),
        ];
    }

    // ========================================
    // ACCIÓN 6: SOLICITAR ENGANCHE
    // ========================================

    private static function getSolicitarEngancheSchema(): array
    {
        return [
            TextInput::make('monto_enganche')
                ->label('Monto del enganche (30-45% del valor)')
                ->numeric()
                ->required()
                ->prefix('$')
                ->helperText('Enganche mínimo 30% del valor total de la propiedad')
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(','),

            DatePicker::make('fecha_limite')
                ->label('Fecha límite para pagar')
                ->required()
                ->minDate(now()->addDays(7))
                ->native(false)
                ->helperText('Mínimo 7 días a partir de hoy'),
        ];
    }

    // ========================================
    // ACCIÓN 7: SUBIR PAGO DE ENGANCHE
    // ========================================

    private static function getSubirPagoEngancheSchema(): array
    {
        return self::getSubirPagoGenericoSchema('enganche');
    }

    // ========================================
    // ACCIÓN 8: SOLICITAR LIQUIDACIÓN
    // ========================================

    private static function getSolicitarLiquidacionSchema(): array
    {
        return [
            TextInput::make('monto_liquidacion')
                ->label('Monto de la liquidación (saldo pendiente)')
                ->numeric()
                ->required()
                ->prefix('$')
                ->helperText('Saldo final para liquidar la propiedad')
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(','),

            DatePicker::make('fecha_limite')
                ->label('Fecha límite para liquidar')
                ->required()
                ->minDate(now()->addDays(7))
                ->native(false),
        ];
    }

    // ========================================
    // ACCIÓN 9: SUBIR PAGO DE LIQUIDACIÓN
    // ========================================

    private static function getSubirPagoLiquidacionSchema(): array
    {
        return self::getSubirPagoGenericoSchema('liquidacion');
    }

    // ========================================
    // ACCIÓN 10: PROGRAMAR ENTREGA
    // ========================================

    private static function getProgramarEntregaSchema(): array
    {
        return [
            DateTimePicker::make('fecha_entrega')
                ->label('Fecha y hora de entrega')
                ->required()
                ->minDate(now())
                ->native(false),

            Textarea::make('observaciones_entrega')
                ->label('Notas para la entrega')
                ->rows(3)
                ->placeholder('Ej: Coordinar con cliente, llevar llaves, etc.'),
        ];
    }

    // ========================================
    // ACCIÓN 11: REGISTRAR ENTREGA FINAL
    // ========================================

    private static function getRegistrarEntregaFinalSchema(): array
    {
        return [
            FileUpload::make('acta_entrega')
                ->label('Acta de entrega física firmada')
                ->disk('public')
                ->directory('entregas')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->required(),

            Textarea::make('observaciones')
                ->label('Observaciones finales')
                ->rows(3),
        ];
    }

    // ========================================
    // SCHEMA: CANCELAR PROCESO
    // ========================================

    private static function getCancelarSchema(): array
    {
        return [
            Select::make('motivo_cancelacion')
                ->label('Motivo de cancelación')
                ->options([
                    'CLIENTE_DESISTIO' => 'Cliente desistió',
                    'NO_PASO_DICTAMEN' => 'No pasó dictamen jurídico',
                    'FALTA_PAGO' => 'No pagó en tiempo',
                    'PROPIEDAD_VENDIDA' => 'Propiedad ya vendida a otro',
                    'OTRO' => 'Otro motivo',
                ])
                ->required()
                ->native(false),

            Textarea::make('detalles_cancelacion')
                ->label('Detalles')
                ->rows(3)
                ->required(),
        ];
    }

    // ========================================
    // HELPER: SCHEMA GENÉRICO DE PAGO
    // ========================================

    private static function getSubirPagoGenericoSchema(string $concepto): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('monto')
                    ->label('Monto pagado')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),

                Select::make('metodo_pago')
                    ->label('Método de pago')
                    ->options([
                        'TRANSFERENCIA' => 'Transferencia',
                        'EFECTIVO' => 'Efectivo',
                        'TARJETA' => 'Tarjeta',
                    ])
                    ->required()
                    ->native(false),
            ]),

            FileUpload::make('comprobante_url')
                ->label('Comprobante de pago')
                ->disk('public')
                ->directory('pagos/' . $concepto)
                ->required(),
        ];
    }

    // ========================================
    // EJECUCIÓN DE ACCIONES
    // ========================================

    private static function executeNextAction(ProcesoVenta $record, array $data): void
    {
        match ($record->estatus) {
            'ACTIVO' => !$record->interesado->archivos()->where('categoria', 'AVISO_PRIVACIDAD')->exists()
                ? self::ejecutarSubirAviso($record, $data)
                : self::ejecutarRegistrarVisita($record, $data),
            'VISITA_REALIZADA' => self::ejecutarGenerarContrato($record, $data),
            'APARTADO_GENERADO' => !$record->archivos()->where('categoria', 'CONTRATO_APARTADO_FIRMADO')->exists()
                ? self::ejecutarSubirContratoFirmado($record, $data)
                : self::ejecutarSubirPagoApartado($record, $data),
            'APARTADO_VALIDADO' => self::ejecutarSolicitarDictamen($record, $data),
            'DICTAMINADO_POSITIVO' => self::ejecutarSolicitarEnganche($record, $data),
            'ENGANCHE_SOLICITADO' => self::ejecutarSubirPagoEnganche($record, $data),
            'COMPRA_FINALIZADA' => self::ejecutarSolicitarLiquidacion($record, $data),
            'LIQUIDACION_SOLICITADA' => self::ejecutarSubirPagoLiquidacion($record, $data),
            'ESCRITURADO' => self::ejecutarProgramarEntrega($record, $data),
            'ENTREGA_PROGRAMADA' => self::ejecutarRegistrarEntregaFinal($record, $data),
            default => null,
        };
    }

    // ========================================
    // EJECUCIÓN 1: SUBIR AVISO
    // ========================================

    private static function ejecutarSubirAviso(ProcesoVenta $record, array $data): void
    {
        $record->interesado->archivos()->create([
            'categoria'       => 'AVISO_PRIVACIDAD',
            'ruta_archivo'    => $data['archivo_temporal'],
            'nombre_original' => 'Aviso_Privacidad_' . $record->interesado->nombre_completo . '.pdf',
        ]);

        Notification::make()
            ->success()
            ->title('Aviso de privacidad subido')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 2: REGISTRAR VISITA
    // ========================================

    private static function ejecutarRegistrarVisita(ProcesoVenta $record, array $data): void
    {
        $nuevoEstatus = $data['resultado_visita'] === 'NO_LE_GUSTO'
            ? 'CANCELADO'
            : 'VISITA_REALIZADA';

        $record->update([
            'fecha_visita' => $data['fecha_visita'],
            'resultado_visita' => $data['resultado_visita'],
            'observaciones_visita' => $data['observaciones_visita'] ?? null,
            'estatus' => $nuevoEstatus,
        ]);

        if ($nuevoEstatus === 'CANCELADO') {
            $record->update([
                'motivo_cancelacion' => 'CLIENTE_DESISTIO',
                'detalles_cancelacion' => 'No le gustó la propiedad en la visita',
                'fecha_cancelacion' => now(),
            ]);

            // Liberar propiedad
            $record->propiedad->update(['estatus_comercial' => 'DISPONIBLE']);
        }

        Notification::make()
            ->success()
            ->title('Visita registrada')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 3: GENERAR CONTRATO
    // ========================================

    private static function ejecutarGenerarContrato(ProcesoVenta $record, array $data): void
    {
        if (empty($record->folio_apartado)) {
            $record->update(['folio_apartado' => 'APT-' . time()]);
        }

        $record->update(['estatus' => 'APARTADO_GENERADO']);

        // No notificamos aquí porque se abre en nueva pestaña
    }

    // ========================================
    // EJECUCIÓN 4: SUBIR CONTRATO FIRMADO
    // ========================================

    private static function ejecutarSubirContratoFirmado(ProcesoVenta $record, array $data): void
    {
        $record->archivos()->create([
            'categoria' => 'CONTRATO_APARTADO_FIRMADO',
            'ruta_archivo' => $data['archivo_temporal'],
            'nombre_original' => 'Contrato_Apartado_' . $record->folio_apartado . '.pdf',
        ]);

        Notification::make()
            ->success()
            ->title('Contrato firmado subido')
            ->body('Ahora sube el comprobante de pago del apartado')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 5: SUBIR PAGO APARTADO
    // ========================================

    private static function ejecutarSubirPagoApartado(ProcesoVenta $record, array $data): void
    {
        $record->pagos()->create([
            'concepto' => 'APARTADO',
            'monto' => $data['monto'],
            'metodo_pago' => $data['metodo_pago'],
            'comprobante_url' => $data['comprobante_url'],
            'estatus' => 'PENDIENTE',
        ]);

        $record->update(['estatus' => 'APARTADO_POR_VALIDAR']);

        Notification::make()
            ->success()
            ->title('Comprobante subido')
            ->body('El pago ha sido enviado a revisión por Contabilidad.')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 6: SOLICITAR DICTAMEN
    // ========================================

    private static function ejecutarSolicitarDictamen(ProcesoVenta $record, array $data): void
    {
        Dictamen::create([
            'tipo_solicitud' => 'VENTA',
            'origen_solicitud' => 'CARTERA',
            'proceso_venta_id' => $record->id,
            'propiedad_id' => $record->propiedad_id,
            'usuario_solicitante_id' => Auth::id(),
            'nombre_proveedor' => $data['nombre_proveedor'],
            'numero_credito' => $data['numero_credito'] ?? null,
            'es_dueno_real' => $data['es_dueno_real'],
            'tiene_posesion' => $data['tiene_posesion'],
            'fecha_ultimo_pago_deudor' => $data['fecha_ultimo_pago_deudor'] ?? null,
            'estatus' => 'PENDIENTE',
        ]);

        $record->update(['estatus' => 'EN_DICTAMINACION']);

        Notification::make()
            ->success()
            ->title('Solicitud enviada')
            ->body('El expediente ha sido enviado a la bandeja de jurídico.')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 7: SOLICITAR ENGANCHE
    // ========================================

    private static function ejecutarSolicitarEnganche(ProcesoVenta $record, array $data): void
    {
        $record->update([
            'monto_enganche_solicitado' => $data['monto_enganche'],
            'fecha_limite_enganche' => $data['fecha_limite'],
            'estatus' => 'ENGANCHE_SOLICITADO',
        ]);

        Notification::make()
            ->success()
            ->title('Enganche solicitado')
            ->body('Se ha notificado al cliente sobre el pago del enganche.')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 8: SUBIR PAGO ENGANCHE
    // ========================================

    private static function ejecutarSubirPagoEnganche(ProcesoVenta $record, array $data): void
    {
        $record->pagos()->create([
            'concepto' => 'ENGANCHE',
            'monto' => $data['monto'],
            'metodo_pago' => $data['metodo_pago'],
            'comprobante_url' => $data['comprobante_url'],
            'estatus' => 'PENDIENTE',
        ]);

        $record->update(['estatus' => 'ENGANCHE_POR_VALIDAR']);

        Notification::make()
            ->success()
            ->title('Pago de enganche subido')
            ->body('Enviado a validación por Contabilidad.')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 9: SOLICITAR LIQUIDACIÓN
    // ========================================

    private static function ejecutarSolicitarLiquidacion(ProcesoVenta $record, array $data): void
    {
        $record->update([
            'monto_liquidacion_solicitado' => $data['monto_liquidacion'],
            'fecha_limite_liquidacion' => $data['fecha_limite'],
            'estatus' => 'LIQUIDACION_SOLICITADA',
        ]);

        Notification::make()
            ->success()
            ->title('Liquidación solicitada')
            ->body('Se ha notificado al cliente sobre el pago final.')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 10: SUBIR PAGO LIQUIDACIÓN
    // ========================================

    private static function ejecutarSubirPagoLiquidacion(ProcesoVenta $record, array $data): void
    {
        $record->pagos()->create([
            'concepto' => 'LIQUIDACION',
            'monto' => $data['monto'],
            'metodo_pago' => $data['metodo_pago'],
            'comprobante_url' => $data['comprobante_url'],
            'estatus' => 'PENDIENTE',
        ]);

        $record->update(['estatus' => 'LIQUIDACION_POR_VALIDAR']);

        Notification::make()
            ->success()
            ->title('Pago de liquidación subido')
            ->body('Enviado a validación por Contabilidad.')
            ->send();
    }

    // ========================================
    // EJECUCIÓN 11: PROGRAMAR ENTREGA
    // ========================================

    private static function ejecutarProgramarEntrega(ProcesoVenta $record, array $data): void
    {
        $record->update([
            'fecha_entrega_programada' => $data['fecha_entrega'],
            'observaciones' => $data['observaciones_entrega'] ?? null,
            'estatus' => 'ENTREGA_PROGRAMADA',
        ]);

        Notification::make()
            ->success()
            ->title('Entrega programada')
            ->body('Fecha: ' . \Carbon\Carbon::parse($data['fecha_entrega'])->format('d/m/Y H:i'))
            ->send();
    }

    // ========================================
    // EJECUCIÓN 12: REGISTRAR ENTREGA FINAL
    // ========================================

    private static function ejecutarRegistrarEntregaFinal(ProcesoVenta $record, array $data): void
    {
        $record->archivos()->create([
            'categoria' => 'ACTA_ENTREGA',
            'ruta_archivo' => $data['acta_entrega'],
            'nombre_original' => 'Acta_Entrega_' . $record->id . '.pdf',
        ]);

        $record->update([
            'fecha_entrega' => now(),
            'estatus' => 'ENTREGADO',
        ]);

        // Convertir a cliente si aún es prospecto
        if ($record->interesado_type === 'App\\Models\\Prospecto') {
            $record->interesado->update(['estatus' => 'CLIENTE']);
        }

        Notification::make()
            ->success()
            ->title('¡Proceso completado!')
            ->body('La propiedad ha sido entregada exitosamente.')
            ->send();
    }

    // ========================================
    // CANCELAR PROCESO
    // ========================================

    private static function cancelarProceso(ProcesoVenta $record, array $data): void
    {
        $record->update([
            'estatus' => 'CANCELADO',
            'motivo_cancelacion' => $data['motivo_cancelacion'],
            'detalles_cancelacion' => $data['detalles_cancelacion'],
            'fecha_cancelacion' => now(),
        ]);

        // Liberar propiedad
        $record->propiedad->update(['estatus_comercial' => 'DISPONIBLE']);

        Notification::make()
            ->warning()
            ->title('Proceso cancelado')
            ->body('La propiedad ha sido liberada.')
            ->send();
    }

    // ========================================
    // HELPER: COLORES DE ESTATUS
    // ========================================

    private static function getEstatusColor(string $estatus): string
    {
        return match ($estatus) {
            'ACTIVO', 'VISITA_REALIZADA', 'VISITA_PROGRAMADA' => 'info',
            'APARTADO_GENERADO', 'APARTADO_POR_VALIDAR' => 'warning',
            'APARTADO_VALIDADO', 'DICTAMINADO_POSITIVO', 'ENGANCHE_PAGADO', 'LIQUIDACION_PAGADA', 'ENTREGADO' => 'success',
            'EN_DICTAMINACION', 'EN_PROCESO_COMPRA', 'EN_ESCRITURACION' => 'primary',
            'ENGANCHE_SOLICITADO', 'LIQUIDACION_SOLICITADA', 'ENTREGA_PROGRAMADA' => 'warning',
            'DICTAMINADO_NEGATIVO', 'CANCELADO' => 'danger',
            default => 'gray',
        };
    }
}
