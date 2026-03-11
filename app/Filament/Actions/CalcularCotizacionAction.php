<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\CatEtapaProcesal;
use App\Models\CatTabuladorCosto;
use App\Models\EsquemaPago;
use App\Models\Propiedad;
use App\Models\User;
use App\Services\CotizadorService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class CalcularCotizacionAction
{
    public static function make(): Action
    {
        return Action::make('calcular_cotizacion')
            ->label('Calcular Precio')
            ->icon('heroicon-o-calculator')
            ->color('success')
            ->visible(function (Propiedad $record) {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                return $user->can('propiedades_calcular_precio') &&
                    $record->estatus_comercial === 'BORRADOR';
            })
            ->modalHeading('Cotizador de Precio de Venta')
            ->modalDescription('Calcula el precio sugerido basado en costos operativos y etapa procesal')
            ->modalWidth('5xl')
            ->slideOver()
            ->closeModalByClickingAway(false)
            ->steps(function (Propiedad $record) {
                $tamanoAuto = $record->calcularTamanoAutomatico();

                return [
                    // STEP 1: DATOS BASE
                    Step::make('Datos Base')
                        ->icon('heroicon-o-information-circle')
                        ->description('Información básica de la propiedad')
                        ->schema([
                            Section::make()
                                ->columns(3)
                                ->schema([
                                    Placeholder::make('numero_credito')
                                        ->label('No. Crédito')
                                        ->content($record->numero_credito),

                                    Placeholder::make('precio_lista_info')
                                        ->label('Precio Lista')
                                        ->content('$' . number_format($record->precio_lista ?? 0, 2)),

                                    Placeholder::make('construccion_m2_info')
                                        ->label('M² Construcción')
                                        ->content($record->construccion_m2 ?? 'N/D'),
                                ]),
                        ]),

                    // STEP 2: CONFIGURACIÓN DEL CÁLCULO
                    Step::make('Configuración')
                        ->icon('heroicon-o-cog')
                        ->description('Parámetros de cotización')
                        ->schema([
                            Section::make()
                                ->columns(2)
                                ->schema([
                                    Select::make('tamano_propiedad')
                                        ->label('Tamaño de la Propiedad')
                                        ->options([
                                            'CHICA' => 'Chica (0-80 m²)',
                                            'MEDIANA' => 'Mediana (81-150 m²)',
                                            'GRANDE' => 'Grande (151-250 m²)',
                                            'MUY_GRANDE' => 'Muy Grande (251+ m²)',
                                        ])
                                        ->default($tamanoAuto)
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->helperText($tamanoAuto ? "✨ Sugerido: {$tamanoAuto}" : 'Selecciona manualmente'),

                                    Select::make('etapa_procesal_id')
                                        ->label('Etapa Procesal')
                                        ->options(
                                            CatEtapaProcesal::paraCotizacion()
                                                ->get()
                                                ->mapWithKeys(fn($etapa) => [
                                                    $etapa->id => sprintf(
                                                        '%s (%s - %s%%)',
                                                        $etapa->nombre,
                                                        str_replace('FASE_', 'F', $etapa->fase_cotizacion),
                                                        $etapa->porcentaje_inversion
                                                    )
                                                ])
                                        )
                                        ->searchable()
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->helperText('Determina el % de incremento'),

                                    TextInput::make('porcentaje_descuento')
                                        ->label('% Descuento')
                                        ->numeric()
                                        ->default(10)
                                        ->minValue(0)
                                        ->maxValue(30)
                                        ->suffix('%')
                                        ->required()
                                        ->live(debounce: 500),

                                    Textarea::make('notas')
                                        ->label('Notas / Observaciones')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // STEP 3: ESQUEMA DE PAGOS
                    Step::make('Esquema de Pagos')
                        ->icon('heroicon-o-credit-card')
                        ->description('Define los pagos sugeridos')
                        ->schema([
                            Section::make('💳 Apartado')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('apartado_monto')
                                        ->label('Monto de Apartado')
                                        ->prefix('$')
                                        ->numeric()
                                        ->default(10000)
                                        ->required()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->helperText('Se descuenta del último pago')
                                        ->live(),

                                    Placeholder::make('apartado_info')
                                        ->label('Información')
                                        ->content(new HtmlString(
                                            '<div class="text-sm text-gray-600 dark:text-gray-400">
                                                El apartado se descuenta automáticamente del último pago configurado.
                                            </div>'
                                        )),
                                ]),

                            Section::make('📋 Pagos Sugeridos')
                                ->schema([
                                    Repeater::make('pagos')
                                        ->label('')
                                        ->schema([
                                            TextInput::make('descripcion')
                                                ->label('Descripción del Pago')
                                                ->required()
                                                ->placeholder('Ej: Estudio de garantía')
                                                ->columnSpan(2),

                                            TextInput::make('porcentaje')
                                                ->label('Porcentaje')
                                                ->suffix('%')
                                                ->numeric()
                                                ->required()
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->live(debounce: 300)
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    self::recalcularTotalPorcentaje($get, $set);
                                                })
                                                ->columnSpan(1),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(3)
                                        ->default(EsquemaPago::getEsquemaDefault())
                                        ->addActionLabel('➕ Agregar Pago')
                                        ->minItems(1)
                                        ->maxItems(10)
                                        ->live()
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn(array $state): ?string =>
                                            $state['descripcion'] ?? 'Nuevo pago'
                                        ),

                                    Placeholder::make('validacion_porcentajes')
                                        ->label('')
                                        ->content(function (Get $get) {
                                            return self::generarValidacionPorcentajes($get);
                                        }),
                                ]),
                        ]),

                    // STEP 4: PREVIEW
                    Step::make('Preview')
                        ->icon('heroicon-o-presentation-chart-bar')
                        ->description('Revisión final de la cotización')
                        ->schema([
                            Placeholder::make('preview_calculo')
                                ->label('Preview del cálculo')
                                ->content(function (Get $get) use ($record) {
                                    return self::generarPreview($record, $get);
                                }),
                        ]),
                ];
            })
            ->action(function (Propiedad $record, array $data) {
                try {
                    DB::transaction(function () use ($record, $data) {
                        // 1. Calcular cotización
                        $cotizadorService = new CotizadorService();

                        $cotizacion = $cotizadorService->calcular(
                            propiedad: $record,
                            tamano: $data['tamano_propiedad'],
                            etapaProcesalId: $data['etapa_procesal_id'],
                            porcentajeDescuento: $data['porcentaje_descuento'],
                            notas: $data['notas'] ?? null
                        );

                        // 2. Crear/actualizar esquema de pagos
                        $esquema = EsquemaPago::crearOActualizar(
                            propiedadId: $record->id,
                            apartado: $data['apartado_monto'],
                            detalles: $data['pagos']
                        );

                        // 3. Calcular montos de los pagos
                        $esquema->calcularMontos($cotizacion->precio_venta_con_descuento);

                        // 4. Actualizar estatus de la propiedad
                        $record->update([
                            'estatus_comercial' => 'EN_REVISION',
                        ]);
                    });

                    Notification::make()
                        ->success()
                        ->title('✅ Cotización Calculada')
                        ->body('Precio calculado y esquema de pagos configurado exitosamente')
                        ->send();

                    // Notificar a aprobadores
                    self::notificarAprobadores($record, $record->cotizaciones()->where('activa', true)->first());

                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error al Calcular')
                        ->body($e->getMessage())
                        ->persistent()
                        ->send();
                }
            });
    }

    /**
     * Recalcular total de porcentajes
     */
    protected static function recalcularTotalPorcentaje(Get $get, Set $set): void
    {
        $pagos = $get('pagos') ?? [];
        $total = collect($pagos)->sum('porcentaje');

        // Trigger para actualizar la vista de validación
        $set('total_porcentaje_hidden', $total);
    }

    /**
     * Generar HTML de validación de porcentajes
     */
    protected static function generarValidacionPorcentajes(Get $get)
    {
        $pagos = $get('pagos') ?? [];
        $total = collect($pagos)->sum('porcentaje');

        $esValido = abs($total - 100) < 0.01;

        return view('filament.validaciones.porcentajes-esquema-pago', [
            'total' => $total,
            'esValido' => $esValido,
        ]);
    }

    /**
     * Generar preview del cálculo usando Blade view
     */
    protected static function generarPreview(Propiedad $record, Get $get)
    {
        $tamano = $get('tamano_propiedad');
        $etapaProcesalId = $get('etapa_procesal_id');
        $porcentajeDescuento = floatval($get('porcentaje_descuento') ?? 0);

        if (!$tamano || !$etapaProcesalId) {
            return new HtmlString('<p class="text-gray-500 text-center py-4">⏳ Completa los campos para ver el preview</p>');
        }

        // Obtener tabulador
        $tabulador = CatTabuladorCosto::getCostosPorTamano($tamano);
        if (!$tabulador) {
            return new HtmlString('<p class="text-red-500">❌ No hay tabulador configurado para este tamaño</p>');
        }

        // Obtener etapa
        $etapa = CatEtapaProcesal::find($etapaProcesalId);
        if (!$etapa) {
            return new HtmlString('<p class="text-red-500">❌ Etapa procesal inválida</p>');
        }

        // Renderizar Blade view
        return view('filament.previews.cotizacion-preview', [
            'record' => $record,
            'tabulador' => $tabulador,
            'etapa' => $etapa,
            'porcentajeDescuento' => $porcentajeDescuento,
        ]);
    }

    /**
     * Notificar a aprobadores (Comercial y Contabilidad)
     */
    protected static function notificarAprobadores(Propiedad $record, $cotizacion): void
    {
        $aprobadoresComercial = User::permission('precios_aprobar_comercial')->get();
        $aprobadoresContabilidad = User::permission('precios_aprobar_contabilidad')->get();

        if ($aprobadoresComercial->isNotEmpty()) {
            Notification::make()
                ->title('🔔 Nueva Cotización por Aprobar')
                ->body("Propiedad: {$record->numero_credito}\nPrecio: $" . number_format($cotizacion->precio_venta_sugerido, 2))
                ->icon('heroicon-o-currency-dollar')
                ->actions([
                    Action::make('revisar')
                        ->button()
                        ->url(PropiedadResource::getUrl('view', ['record' => $record])),
                ])
                ->sendToDatabase($aprobadoresComercial);
        }

        if ($aprobadoresContabilidad->isNotEmpty()) {
            Notification::make()
                ->title('🔔 Nueva Cotización por Aprobar')
                ->body("Propiedad: {$record->numero_credito}\nPrecio: $" . number_format($cotizacion->precio_venta_sugerido, 2))
                ->icon('heroicon-o-currency-dollar')
                ->actions([
                    Action::make('revisar')
                        ->button()
                        ->url(PropiedadResource::getUrl('view', ['record' => $record])),
                ])
                ->sendToDatabase($aprobadoresContabilidad);
        }
    }
}
