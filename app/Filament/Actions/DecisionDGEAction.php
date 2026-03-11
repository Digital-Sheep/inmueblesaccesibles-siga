<?php

namespace App\Filament\Actions;

use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class DecisionDGEAction
{
    public static function make(): Action
    {
        return Action::make('decision_dge')
            ->label('Tomar decisión DGE')
            ->icon('heroicon-o-scale')
            ->color('warning')
            ->visible(
                function (Propiedad $record) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return $record->precio_requiere_decision_dge &&
                        $user->can('precios_decision_final');
                }
            )
            ->modalHeading('Decisión final DGE')
            ->modalDescription('Como DGE, tu decisión es final y resolverá el conflicto de aprobaciones.')
            ->modalWidth('3xl')
            ->schema([
                // Mostrar información del conflicto
                Placeholder::make('info_conflicto')
                    ->label('Situación Actual')
                    ->content(function (Propiedad $record) {
                        $comercial = $record->aprobacionesPrecio
                            ->firstWhere('tipo_aprobador', 'COMERCIAL');

                        $contabilidad = $record->aprobacionesPrecio
                            ->firstWhere('tipo_aprobador', 'CONTABILIDAD');

                        $precioOriginal = $record->precio_venta_con_descuento;

                        return view('filament.components.conflicto-aprobaciones', [
                            'comercial' => $comercial,
                            'contabilidad' => $contabilidad,
                            'precioOriginal' => $precioOriginal,
                        ]);
                    }),

                // Opciones de decisión
                Select::make('decision')
                    ->label('¿Qué decisión tomas?')
                    ->options(function (Propiedad $record) {
                        $precioOriginal = number_format($record->precio_venta_con_descuento, 2);

                        $options = [
                            'aprobar_original' => '✅ Aprobar precio original ($' . $precioOriginal . ')',
                        ];

                        // Agregar opciones de precios sugeridos si existen
                        $aprobaciones = $record->aprobacionesPrecio;

                        foreach ($aprobaciones as $apr) {
                            if ($apr->precio_sugerido_alternativo) {
                                $key = 'aprobar_' . strtolower($apr->tipo_aprobador);
                                $options[$key] = '💡 Aprobar sugerencia ' . $apr->tipo_aprobador .
                                    ' ($' . number_format($apr->precio_sugerido_alternativo, 2) . ')';
                            }
                        }

                        $options['precio_personalizado'] = '🎯 Establecer precio personalizado';
                        $options['rechazar_todo'] = '❌ Rechazar todo y volver a cotizar';

                        return $options;
                    })
                    ->required()
                    ->reactive()
                    ->native(false)
                    ->helperText('Selecciona la opción que mejor resuelva el conflicto'),

                // Campo de precio personalizado (solo visible si se selecciona esa opción)
                TextInput::make('precio_personalizado_monto')
                    ->label('Precio personalizado')
                    ->prefix('$')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->helperText('Ingresa el precio que consideras adecuado')
                    ->visible(fn(Get $get) => $get('decision') === 'precio_personalizado')
                    ->live(debounce: 500),

                // Información adicional según decisión
                Placeholder::make('info_decision')
                    ->label('')
                    ->content(function (Get $get) {
                        $decision = $get('decision');

                        if (!$decision) {
                            return '';
                        }

                        $mensajes = [
                            'aprobar_original' => '✅ Se aprobará el precio calculado originalmente. La propiedad pasará a estado DISPONIBLE.',
                            'aprobar_comercial' => '💡 Se usará el precio sugerido por Comercial. SE RECALCULARÁN TODOS LOS CAMPOS DE PRECIO.',
                            'aprobar_contabilidad' => '💡 Se usará el precio sugerido por Contabilidad. SE RECALCULARÁN TODOS LOS CAMPOS DE PRECIO.',
                            'precio_personalizado' => '🎯 Se usará el precio personalizado que establezcas. SE RECALCULARÁN TODOS LOS CAMPOS DE PRECIO.',
                            'rechazar_todo' => '❌ Se rechazarán todas las cotizaciones. La propiedad volverá a estado BORRADOR.',
                        ];

                        $mensaje = $mensajes[$decision] ?? '';
                        $color = str_contains($decision, 'aprobar') || $decision === 'precio_personalizado' ? '#dcfce7' : '#fef2f2';

                        return new HtmlString("
                            <div style='background: {$color}; border-radius: 8px; padding: 12px; margin-top: 8px;'>
                                <strong>Resultado:</strong> {$mensaje}
                            </div>
                        ");
                    })
                    ->visible(fn(Get $get) => $get('decision') !== null),

                // Justificación
                Textarea::make('comentarios_dge')
                    ->label('Justificación de tu decisión')
                    ->placeholder('Explica brevemente por qué tomaste esta decisión...')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->helperText('Este comentario quedará registrado en el historial de la propiedad'),
            ])
            ->action(function (Propiedad $record, array $data) {
                try {
                    DB::transaction(function () use ($record, $data) {
                        $decision = $data['decision'];
                        $comentarios = $data['comentarios_dge'];

                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        if ($decision === 'aprobar_original') {
                            // OPCIÓN 1: Aprobar precio original
                            $record->update([
                                'precio_aprobado' => true,
                                'precio_fecha_aprobacion' => now(),
                                'precio_requiere_decision_dge' => false,
                                'estatus_comercial' => 'DISPONIBLE',
                            ]);

                            // Marcar todas las aprobaciones como aprobadas por decisión DGE
                            foreach ($record->aprobacionesPrecio as $aprobacion) {
                                $nuevoComentario = ($aprobacion->comentarios ? $aprobacion->comentarios . "\n\n" : '') .
                                    "[DECISIÓN DGE - {$user->name}] Aprobado por decisión final: {$comentarios}";

                                $aprobacion->update([
                                    'estatus' => 'APROBADO',
                                    'comentarios' => $nuevoComentario,
                                ]);
                            }

                            Notification::make()
                                ->success()
                                ->title('✅ Precio original aprobado')
                                ->body('La propiedad está ahora DISPONIBLE para venta.')
                                ->send();
                        } elseif (str_starts_with($decision, 'aprobar_')) {
                            // OPCIÓN 2: Aprobar una de las sugerencias
                            $tipo = str_replace('aprobar_', '', $decision);

                            $aprobacion = $record->aprobacionesPrecio()
                                ->where('tipo_aprobador', strtoupper($tipo))
                                ->first();

                            if ($aprobacion && $aprobacion->precio_sugerido_alternativo) {
                                $precioNuevo  = $aprobacion->precio_sugerido_alternativo;

                                self::recalcularCamposPrecio($record, $precioNuevo, $comentarios, $user);

                                // // Marcar que requiere acción manual
                                // $record->update([
                                //     'precio_requiere_decision_dge' => false,
                                //     'estatus_comercial' => 'BORRADOR', // Volver a borrador para recotizar
                                // ]);

                                // // Desactivar cotizaciones actuales
                                // $record->cotizaciones()->update(['activa' => false]);
                                // $record->aprobacionesPrecio()->delete();

                                Notification::make()
                                    ->warning()
                                    ->title('💡 Precio Sugerido Aprobado')
                                    ->body("Nuevo precio: $" . number_format($precioNuevo, 2) . ". Todos los campos han sido recalculados.")
                                    ->persistent()
                                    ->send();
                            }
                        } elseif ($decision === 'precio_personalizado') {
                            // OPCIÓN 3: Precio personalizado por DGE
                            $precioPersonalizado = floatval(str_replace(',', '', $data['precio_personalizado_monto']));

                            self::recalcularCamposPrecio($record, $precioPersonalizado, $comentarios, $user);

                            // Marcar que requiere acción manual
                            // $record->update([
                            //     'precio_requiere_decision_dge' => false,
                            //     'estatus_comercial' => 'BORRADOR', // Volver a borrador para recotizar
                            // ]);

                            // Desactivar cotizaciones actuales
                            // $record->cotizaciones()->update(['activa' => false]);
                            // $record->aprobacionesPrecio()->delete();

                            Notification::make()
                                ->warning()
                                ->title('🎯 Precio personalizado establecido')
                                ->body("Nuevo precio: $" . number_format($precioPersonalizado, 2) . ". Todos los campos han sido recalculados.")
                                ->persistent()
                                ->send();
                        } elseif ($decision === 'rechazar_todo') {
                            // OPCIÓN 3: Rechazar todo y volver a borrador
                            $record->update([
                                'estatus_comercial' => 'BORRADOR',
                                'precio_calculado' => false,
                                'precio_aprobado' => false,
                                'precio_requiere_decision_dge' => false,
                                'precio_sin_remodelacion' => null,
                                'precio_venta_sugerido' => null,
                                'precio_venta_con_descuento' => null,
                                'precio_fecha_aprobacion' => null,
                            ]);

                            $record->cotizaciones()->update(['activa' => false]);
                            $record->aprobacionesPrecio()->delete();

                            Notification::make()
                                ->warning()
                                ->title('❌ Cotización rechazada')
                                ->body('La propiedad ha vuelto a BORRADOR. Se debe recotizar desde cero.')
                                ->send();
                        }
                    });
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('❌ Error al aplicar decisión')
                        ->body('No se pudo procesar la decisión: ' . $e->getMessage())
                        ->persistent()
                        ->send();
                }
            });
    }

    protected static function recalcularCamposPrecio(Propiedad $record, float $precioNuevo, string $comentarios, $user): void
    {
        // Obtener la cotización activa para calcular proporciones
        $cotizacionActiva = $record->cotizaciones()->where('activa', true)->first();

        if (!$cotizacionActiva) {
            throw new \Exception('No hay cotización activa para recalcular');
        }

        $precioConDescuentoOriginal = $cotizacionActiva->precio_venta_con_descuento;
        $factorAjuste = $precioNuevo / $precioConDescuentoOriginal;

        $precioSugeridoNuevo = $cotizacionActiva->precio_venta_sugerido * $factorAjuste;

        $precioSinRemodelacionNuevo = $cotizacionActiva->precio_sin_remodelacion * $factorAjuste;

        $porcentajeDescuento = $cotizacionActiva->porcentaje_descuento ?? 0;

        // Actualizar la propiedad
        $record->update([
            'precio_venta_sugerido' => $precioSugeridoNuevo,
            'precio_sin_remodelacion' => $precioSinRemodelacionNuevo,
            'precio_venta_con_descuento' => $precioNuevo,
            'porcentaje_descuento' => $porcentajeDescuento,
            'precio_aprobado' => true,
            'precio_fecha_aprobacion' => now(),
            'precio_requiere_decision_dge' => false,
            'estatus_comercial' => 'DISPONIBLE',
        ]);

        // Actualizar la cotización activa
        $cotizacionActiva->update([
            'precio_venta_sugerido' => $precioSugeridoNuevo,
            'precio_sin_remodelacion' => $precioSinRemodelacionNuevo,
            'precio_venta_con_descuento' => $precioNuevo,
            'porcentaje_descuento' => $porcentajeDescuento,
            'notas' => ($cotizacionActiva->notas ?? '') . "\n\n[DGE - {$user->name}] Precio ajustado: {$comentarios}",
        ]);

        // Marcar todas las aprobaciones como aprobadas
        foreach ($record->aprobacionesPrecio as $aprobacion) {
            $nuevoComentario = ($aprobacion->comentarios ? $aprobacion->comentarios . "\n\n" : '') .
                "[DECISIÓN DGE - {$user->name}] Precio ajustado y aprobado: {$comentarios}";

            $aprobacion->update([
                'aprobado' => true,
                'comentarios' => $nuevoComentario,
            ]);
        }
    }
}
