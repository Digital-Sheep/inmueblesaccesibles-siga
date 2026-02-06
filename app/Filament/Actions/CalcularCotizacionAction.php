<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\CatEtapaProcesal;
use App\Models\CatTabuladorCosto;
use App\Models\Propiedad;
use App\Models\User;
use App\Services\CotizadorService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CalcularCotizacionAction
{
    public static function make(): Action
    {
        return Action::make('calcular_cotizacion')
            ->label('Calcular Precio')
            ->icon('heroicon-o-calculator')
            ->color('success')
            ->closeModalByClickingAway(false)
            ->visible(
                function (Propiedad $record) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return $user->can('propiedades_calcular_precio') &&
                        $record->estatus_comercial === 'BORRADOR';
                }
            )
            ->modalHeading('Cotizador de precio de venta')
            ->modalDescription('Calcula el precio sugerido basado en costos operativos y etapa procesal')
            ->modalWidth('5xl')
            ->slideOver()
            ->schema(function (Propiedad $record) {
                // Calcular tamaño automático
                $tamanoAuto = $record->calcularTamanoAutomatico();

                return [
                    // SECCIÓN 1: DATOS BASE
                    Section::make('📋 Datos base de la propiedad')
                        ->schema([
                            Grid::make(4)
                                ->schema([
                                    Placeholder::make('numero_credito')
                                        ->label('No. crédito')
                                        ->content($record->numero_credito),

                                    Placeholder::make('precio_lista_info')
                                        ->label('Precio lista')
                                        ->content('$' . number_format($record->precio_lista ?? 0, 2)),

                                    Placeholder::make('construccion_m2_info')
                                        ->label('M² construcción')
                                        ->content($record->construccion_m2 ?? 'N/D'),

                                    Placeholder::make('valor_comercial_info')
                                        ->label('Valor comercial')
                                        ->content('$' . number_format($record->precio_valor_comercial ?? 0, 2)),
                                ]),
                        ])
                        ->collapsible(),

                    // SECCIÓN 2: CONFIGURACIÓN DEL CÁLCULO
                    Section::make('⚙️ Configuración del cálculo')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('tamano_propiedad')
                                        ->label('Tamaño de la propiedad')
                                        ->options([
                                            'CHICA' => 'Chica (0-80 m²)',
                                            'MEDIANA' => 'Mediana (81-150 m²)',
                                            'GRANDE' => 'Grande (151-250 m²)',
                                            'MUY_GRANDE' => 'Muy grande (251+ m²)',
                                        ])
                                        ->default($tamanoAuto)
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->helperText($tamanoAuto ? "✨ Sugerido automáticamente: {$tamanoAuto}" : 'Selecciona manualmente'),

                                    Select::make('etapa_procesal_id')
                                        ->label('Etapa procesal')
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
                                        ->helperText('Determina el % de incremento por inversión'),

                                    TextInput::make('porcentaje_descuento')
                                        ->label('% Descuento')
                                        ->numeric()
                                        ->default(10)
                                        ->minValue(0)
                                        ->maxValue(30)
                                        ->suffix('%')
                                        ->required()
                                        ->live(debounce: 500)
                                        ->helperText('Ajusta el descuento para ver utilidad en tiempo real'),

                                    Textarea::make('notas')
                                        ->label('Notas / Observaciones')
                                        ->rows(2)
                                        ->placeholder('Comentarios sobre esta cotización...'),
                                ]),
                        ]),

                    // SECCIÓN 3: PREVIEW DEL CÁLCULO (SOLO LECTURA)
                    Section::make('📊 Preview del cálculo')
                        ->schema([
                            Placeholder::make('preview_calculo')
                                ->label('')
                                ->content(function (Get $get) use ($record) {
                                    return self::generarPreview($record, $get);
                                }),
                        ])
                        ->visible(
                            fn(Get $get) =>
                            $get('tamano_propiedad') &&
                                $get('etapa_procesal_id') &&
                                $get('porcentaje_descuento') !== null
                        ),
                ];
            })
            ->action(function (Propiedad $record, array $data) {
                try {
                    $cotizadorService = new CotizadorService();

                    $cotizacion = $cotizadorService->calcular(
                        propiedad: $record,
                        tamano: $data['tamano_propiedad'],
                        etapaProcesalId: $data['etapa_procesal_id'],
                        porcentajeDescuento: $data['porcentaje_descuento'],
                        notas: $data['notas'] ?? null
                    );

                    // Actualizar estatus de la propiedad
                    $record->update([
                        'estatus_comercial' => 'EN_REVISION',
                    ]);

                    Notification::make()
                        ->success()
                        ->title('✅ Cotización calculada')
                        ->body(sprintf(
                            "Precio sugerido: $%s\nPrecio con descuento: $%s\nUtilidad: %s%%",
                            number_format($cotizacion->precio_venta_sugerido, 2),
                            number_format($cotizacion->precio_venta_con_descuento, 2),
                            number_format($cotizacion->porcentaje_utilidad, 2)
                        ))
                        ->send();

                    // Notificar a aprobadores
                    self::notificarAprobadores($record, $cotizacion);
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Error al calcular')
                        ->body($e->getMessage())
                        ->persistent()
                        ->send();
                }
            })
            ->stickyModalFooter(true);
    }

    /**
     * Generar HTML del preview del cálculo
     */
    protected static function generarPreview(Propiedad $record, Get $get): HtmlString
    {
        $tamano = $get('tamano_propiedad');
        $etapaProcesalId = $get('etapa_procesal_id');
        $porcentajeDescuento = $get('porcentaje_descuento') ?? 0;

        if (!$tamano || !$etapaProcesalId) {
            return new HtmlString('<p class="text-gray-500">Completa los campos para ver el preview...</p>');
        }

        // Obtener costos
        $tabulador = CatTabuladorCosto::getCostosPorTamano($tamano);
        if (!$tabulador) {
            return new HtmlString('<p class="text-red-500">❌ No se encontraron costos para este tamaño</p>');
        }

        // Obtener etapa
        $etapa = CatEtapaProcesal::find($etapaProcesalId);
        if (!$etapa) {
            return new HtmlString('<p class="text-red-500">❌ Etapa procesal inválida</p>');
        }

        // CALCULAR
        $precioBase = $record->precio_lista ?? 0;
        $porcentajeInversion = $etapa->porcentaje_inversion;

        $costoRemodelacion = $tabulador->costo_remodelacion;
        $costoLuz = $tabulador->costo_luz;
        $costoAgua = $tabulador->costo_agua;
        $costoPredial = $tabulador->costo_predial;
        $costoGastosJuridicos = $tabulador->costo_gastos_juridicos;

        $totalCostos = $costoRemodelacion + $costoLuz + $costoAgua + $costoPredial + $costoGastosJuridicos;
        $costosSinRemodelacion = $costoLuz + $costoAgua + $costoPredial + $costoGastosJuridicos;

        $montoInversion = $precioBase * ($porcentajeInversion / 100);

        $precioSinRemodelacion = $precioBase + $costosSinRemodelacion + $montoInversion;
        $precioVentaSugerido = $precioBase + $totalCostos + $montoInversion;
        $precioVentaConDescuento = $precioVentaSugerido * (1 - ($porcentajeDescuento / 100));

        $costoTotal = $precioBase + $totalCostos;
        $utilidadConDescuento = $precioVentaConDescuento - $costoTotal;
        $porcentajeUtilidad = ($utilidadConDescuento / $precioVentaConDescuento) * 100;

        // GENERAR HTML CON ESTILOS INLINE
        $html = '
        <div style="background: white; border-radius: 8px; border: 1px solid #e5e7eb; padding: 24px;">

            <!-- Precio Base -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; margin-bottom: 16px;">
                <span style="font-weight: 500; color: #374151;">💵 Precio base (Lista)</span>
                <span style="font-size: 1.125rem; font-weight: 700; color: #111827;">$' . number_format($precioBase, 2) . '</span>
            </div>

            <!-- Costos Desglosados -->
            <div style="background: #eff6ff; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-weight: 600; color: #1e40af; margin-bottom: 12px;">📋 Costos Operativos</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.875rem; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #4b5563;">Remodelación:</span>
                        <span style="font-weight: 500;">$' . number_format($costoRemodelacion, 2) . '</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #4b5563;">Luz:</span>
                        <span style="font-weight: 500;">$' . number_format($costoLuz, 2) . '</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #4b5563;">Agua:</span>
                        <span style="font-weight: 500;">$' . number_format($costoAgua, 2) . '</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #4b5563;">Predial:</span>
                        <span style="font-weight: 500;">$' . number_format($costoPredial, 2) . '</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; grid-column: span 2;">
                        <span style="color: #4b5563;">Gastos jurídicos:</span>
                        <span style="font-weight: 500;">$' . number_format($costoGastosJuridicos, 2) . '</span>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #bfdbfe; font-weight: 700;">
                    <span>Total costos:</span>
                    <span style="color: #1e40af;">$' . number_format($totalCostos, 2) . '</span>
                </div>
            </div>

            <!-- Incremento por Inversión -->
            <div style="background: #faf5ff; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-weight: 600; color: #6b21a8;">💎 Incremento por inversión</span>
                        <span style="font-size: 0.875rem; color: #9333ea; margin-left: 8px;">(' . $porcentajeInversion . '%)</span>
                    </div>
                    <span style="font-size: 1.125rem; font-weight: 700; color: #6b21a8;">$' . number_format($montoInversion, 2) . '</span>
                </div>
            </div>

            <!-- Precios Calculados -->
            <div style="padding-top: 12px; border-top: 2px solid #e5e7eb; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <span style="font-weight: 500; color: #166534;">🏠 Precio sin remodelación</span>
                    <span style="font-size: 1.25rem; font-weight: 700; color: #14532d;">$' . number_format($precioSinRemodelacion, 2) . '</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #eff6ff; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <span style="font-weight: 500; color: #1e40af;">✨ Precio venta sugerido</span>
                    <span style="font-size: 1.25rem; font-weight: 700; color: #1e3a8a;">$' . number_format($precioVentaSugerido, 2) . '</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #fff7ed; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <div>
                        <span style="font-weight: 500; color: #9a3412;">🎯 Precio con descuento</span>
                        <span style="font-size: 0.875rem; color: #ea580c; margin-left: 8px;">(-' . $porcentajeDescuento . '%)</span>
                    </div>
                    <span style="font-size: 1.5rem; font-weight: 700; color: #7c2d12;">$' . number_format($precioVentaConDescuento, 2) . '</span>
                </div>
            </div>

            <!-- Utilidad -->
            <div style="display: flex; justify-content: space-between; align-items: center; background: #fef9c3; border-radius: 8px; padding: 16px; border: 2px solid #fde047;">
                <span style="font-size: 1.125rem; font-weight: 700; color: #713f12;">💰 Utilidad esperada</span>
                <div style="text-align: right;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #713f12;">' . number_format($porcentajeUtilidad, 2) . '%</div>
                    <div style="font-size: 0.875rem; color: #854d0e;">$' . number_format($utilidadConDescuento, 2) . '</div>
                </div>
            </div>

        </div>
        ';

        $valorComercial = $record->precio_valor_comercial;

        if ($valorComercial && $valorComercial > 0) {
            $html .= '
    <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid #d1d5db;">
        <div style="font-weight: bold; font-size: 1.125rem; color: #111827; margin-bottom: 16px;">🏘️ Comparativo con valor comercial</div>

        <div style="background-color: #dbeafe; border-radius: 8px; padding: 16px; margin-bottom: 16px; text-align: center;">
            <div style="font-size: 0.875rem; color: #1d4ed8; margin-bottom: 4px;">Valor de mercado de referencia</div>
            <div style="font-size: 1.5rem; font-weight: bold; color: #1e3a8a;">$' . number_format($valorComercial, 2) . '</div>
        </div>';

            // Comparativo 1: Sin Remodelación
            $porcentajeSinRemo = (($valorComercial - $precioSinRemodelacion) / $valorComercial) * 100;
            $esRemateSinRemo = $porcentajeSinRemo >= 35;
            $colorSinRemo = $esRemateSinRemo ? '#059669' : '#dc2626';
            $bgSinRemo = $esRemateSinRemo ? '#f0fdf4' : '#fef2f2';
            $iconoSinRemo = $esRemateSinRemo ? '✅' : '⚠️';

            $html .= '
    <div style="background: ' . $bgSinRemo . '; border-left: 4px solid ' . $colorSinRemo . '; border-radius: 4px; padding: 12px; margin-bottom: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1;"><span style="font-weight: 600;">Sin remodelación:</span> $' . number_format($precioSinRemodelacion, 2) . '</div>
            <div style="font-weight: bold; color: ' . $colorSinRemo . ';">' . $iconoSinRemo . ' ' . number_format($porcentajeSinRemo, 2) . '% debajo</div>
        </div>
    </div>';

            // Comparativo 2: Precio Sugerido
            $porcentajeSugerido = (($valorComercial - $precioVentaSugerido) / $valorComercial) * 100;
            $esRemateSugerido = $porcentajeSugerido >= 35;
            $colorSugerido = $esRemateSugerido ? '#059669' : '#dc2626';
            $bgSugerido = $esRemateSugerido ? '#f0fdf4' : '#fef2f2';
            $iconoSugerido = $esRemateSugerido ? '✅' : '⚠️';

            $html .= '
    <div style="background: ' . $bgSugerido . '; border-left: 4px solid ' . $colorSugerido . '; border-radius: 4px; padding: 12px; margin-bottom: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1;"><span style="font-weight: 600;">Precio sugerido:</span> $' . number_format($precioVentaSugerido, 2) . '</div>
            <div style="font-weight: bold; color: ' . $colorSugerido . ';">' . $iconoSugerido . ' ' . number_format($porcentajeSugerido, 2) . '% debajo</div>
        </div>
    </div>';

            // Comparativo 3: Con Descuento
            $porcentajeDesc = (($valorComercial - $precioVentaConDescuento) / $valorComercial) * 100;
            $esRemateDesc = $porcentajeDesc >= 35;
            $colorDesc = $esRemateDesc ? '#059669' : '#dc2626';
            $bgDesc = $esRemateDesc ? '#f0fdf4' : '#fef2f2';
            $iconoDesc = $esRemateDesc ? '✅' : '⚠️';

            $html .= '
    <div style="background: ' . $bgDesc . '; border-left: 4px solid ' . $colorDesc . '; border-radius: 4px; padding: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1;"><span style="font-weight: 600;">Con descuento:</span> $' . number_format($precioVentaConDescuento, 2) . '</div>
            <div style="font-weight: bold; color: ' . $colorDesc . ';">' . $iconoDesc . ' ' . number_format($porcentajeDesc, 2) . '% debajo</div>
        </div>
    </div>

    <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 4px; padding: 12px; margin-top: 16px; font-size: 0.875rem; color: #92400e;">
        <strong>Nota:</strong> Para considerarse remate, el precio debe estar al menos <strong>35% debajo</strong> del valor comercial de mercado.
    </div>
</div>';
        }

        $html .= '
    ';

        return new HtmlString($html);
    }

    /**
     * Notificar a aprobadores (Comercial y Contabilidad)
     */
    protected static function notificarAprobadores(Propiedad $record, $cotizacion): void
    {
        // Obtener usuarios con permisos de aprobación
        $aprobadoresComercial = User::permission('precios_aprobar_comercial')->get();
        $aprobadoresContabilidad = User::permission('precios_aprobar_contabilidad')->get();

        if ($aprobadoresComercial->isNotEmpty()) {
            Notification::make()
                ->title('🔔 Nueva cotización por aprobar')
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
                ->title('🔔 Nueva cotización por aprobar')
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
