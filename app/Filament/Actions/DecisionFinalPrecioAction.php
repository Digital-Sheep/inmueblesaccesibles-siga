<?php

namespace App\Filament\Actions;

use App\Models\Propiedad;
use App\Services\CotizadorService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DecisionFinalPrecioAction
{
    public static function make(): Action
    {
        return Action::make('decision_final_precio')
            ->label('⚖️ Decisión Final')
            ->icon('heroicon-o-scale')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Decisión Final de Precio')
            ->modalDescription('Como DGE, debes tomar la decisión final sobre el precio de esta propiedad.')
            ->modalIcon('heroicon-o-shield-check')
            ->modalWidth('3xl')
            ->form(fn(Propiedad $record) => [
                Section::make('Resumen de Aprobaciones')
                    ->description('Revisa los comentarios de cada área')
                    ->schema([
                        Placeholder::make('resumen')
                            ->label('')
                            ->content(fn() => self::getResumenAprobaciones($record)),
                    ])
                    ->collapsible(),

                Section::make('Tu Decisión')
                    ->schema([
                        Radio::make('decision')
                            ->label('¿Qué decides?')
                            ->options([
                                'APROBAR_SUGERIDO' => '✅ Aprobar precio sugerido original: $' . number_format($record->precio_venta_con_descuento, 2),
                                'APROBAR_ALTERNATIVO' => '💡 Aprobar un precio alternativo (de las sugerencias)',
                                'NUEVO_PRECIO' => '🔄 Solicitar nueva cotización',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(
                                fn($state, $set) =>
                                $state !== 'APROBAR_ALTERNATIVO' ? $set('precio_final', null) : null
                            ),

                        TextInput::make('precio_final')
                            ->label('Precio Final Aprobado')
                            ->prefix('$')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required(fn($get) => $get('decision') === 'APROBAR_ALTERNATIVO')
                            ->visible(fn($get) => $get('decision') === 'APROBAR_ALTERNATIVO')
                            ->helperText(fn() => self::getPreciosSugeridos($record)),

                        Textarea::make('comentarios_dge')
                            ->label('Comentarios de la Decisión')
                            ->placeholder('Explica tu decisión...')
                            ->required()
                            ->rows(4)
                            ->maxLength(1000),
                    ]),
            ])
            ->visible(
                function (Propiedad $record) {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();

                    return $user->can('precios_decision_final') &&
                        $record->precio_requiere_decision_dge;
                }
            )
            ->action(function (Propiedad $record, array $data) {
                $decision = $data['decision'];
                $comentarios = $data['comentarios_dge'];

                switch ($decision) {
                    case 'APROBAR_SUGERIDO':
                        self::aprobarPrecioSugerido($record, $comentarios);
                        break;

                    case 'APROBAR_ALTERNATIVO':
                        $precioFinal = floatval(str_replace(',', '', $data['precio_final']));
                        self::aprobarPrecioAlternativo($record, $precioFinal, $comentarios);
                        break;

                    case 'NUEVO_PRECIO':
                        self::solicitarNuevaCotizacion($record, $comentarios);
                        break;
                }
            });
    }

    /**
     * Obtener resumen de las aprobaciones
     */
    protected static function getResumenAprobaciones(Propiedad $record): string
    {
        $html = '<div style="space-y: 12px;">';

        foreach ($record->aprobacionesPrecio as $aprobacion) {
            $color = match ($aprobacion->estatus) {
                'APROBADO' => 'green',
                'RECHAZADO' => 'red',
                default => 'gray',
            };

            $html .= '<div style="border-left: 4px solid ' . $color . '; padding-left: 12px; margin-bottom: 12px;">';
            $html .= '<strong>' . $aprobacion->tipo_aprobador . ':</strong> ';
            $html .= '<span style="color: ' . $color . ';">' . $aprobacion->estatus . '</span><br>';

            if ($aprobacion->precio_sugerido_alternativo) {
                $html .= '<strong>Precio sugerido:</strong> $' . number_format($aprobacion->precio_sugerido_alternativo, 2) . '<br>';
            }

            if ($aprobacion->comentarios) {
                $html .= '<em>' . $aprobacion->comentarios . '</em>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return new HtmlString($html);
    }

    /**
     * Obtener precios sugeridos por las áreas
     */
    protected static function getPreciosSugeridos(Propiedad $record): string
    {
        $sugerencias = $record->aprobacionesPrecio()
            ->whereNotNull('precio_sugerido_alternativo')
            ->get()
            ->map(
                fn($a) =>
                $a->tipo_aprobador . ': $' . number_format($a->precio_sugerido_alternativo, 2)
            )
            ->join(' | ');

        return $sugerencias ?: 'No hay precios alternativos sugeridos';
    }

    /**
     * Aprobar el precio sugerido original
     */
    protected static function aprobarPrecioSugerido(Propiedad $record, string $comentarios): void
    {
        $record->update([
            'precio_aprobado' => true,
            'precio_fecha_aprobacion' => now(),
            'precio_requiere_decision_dge' => false,
        ]);

        // Registrar decisión en las aprobaciones
        foreach ($record->aprobacionesPrecio as $aprobacion) {
            $aprobacion->update([
                'comentarios' => ($aprobacion->comentarios ?? '') . "\n\n[DGE] " . $comentarios,
            ]);
        }

        Notification::make()
            ->success()
            ->title('✅ Precio Aprobado por DGE')
            ->body('Se aprobó el precio sugerido original.')
            ->send();
    }

    /**
     * Aprobar un precio alternativo
     */
    protected static function aprobarPrecioAlternativo(Propiedad $record, float $precioFinal, string $comentarios): void
    {
        // Actualizar el precio en la propiedad
        $record->update([
            'precio_venta_con_descuento' => $precioFinal,
            'precio_aprobado' => true,
            'precio_fecha_aprobacion' => now(),
            'precio_requiere_decision_dge' => false,
        ]);

        // Registrar en cotización activa
        if ($record->cotizacionActiva) {
            $record->cotizacionActiva->update([
                'precio_venta_con_descuento' => $precioFinal,
            ]);
        }

        Notification::make()
            ->success()
            ->title('✅ Precio Alternativo Aprobado')
            ->body("Nuevo precio: $" . number_format($precioFinal, 2))
            ->send();
    }

    /**
     * Solicitar nueva cotización
     */
    protected static function solicitarNuevaCotizacion(Propiedad $record, string $comentarios): void
    {
        // Resetear estado
        $record->update([
            'precio_calculado' => false,
            'precio_aprobado' => false,
            'precio_requiere_decision_dge' => false,
        ]);

        // Eliminar aprobaciones actuales
        $record->aprobacionesPrecio()->delete();

        Notification::make()
            ->warning()
            ->title('🔄 Nueva Cotización Solicitada')
            ->body('Se solicitó recalcular el precio. Las aprobaciones fueron reseteadas.')
            ->send();
    }
}
