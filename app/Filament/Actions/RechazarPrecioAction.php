<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\AprobacionPrecio;
use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RechazarPrecioAction
{
    public static function make(): Action
    {
        return Action::make('rechazar_precio')
            ->label('Rechazar Precio')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Rechazar Precio de Venta')
            ->modalDescription(
                fn(Propiedad $record) =>
                "⚠️ Al rechazar, el precio requerirá aprobación de DGE.\n\n" .
                    "Precio actual sugerido: $" . number_format($record->precio_venta_sugerido, 2)
            )
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalWidth('lg')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('precio_actual')
                            ->label('Precio Actual')
                            ->prefix('$')
                            ->disabled()
                            ->default(fn(Propiedad $record) => number_format($record->precio_venta_con_descuento, 2))
                            ->columnSpan(1),

                        TextInput::make('precio_sugerido_alternativo')
                            ->label('Tu Precio Sugerido')
                            ->prefix('$')
                            ->numeric()
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->helperText('Indica el precio que consideras adecuado')
                            ->columnSpan(1),
                    ]),

                Textarea::make('comentarios')
                    ->label('Justificación del Rechazo')
                    ->placeholder('Explica por qué rechazas el precio y por qué sugieres el alternativo...')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->helperText('Estos comentarios ayudarán a DGE a tomar la decisión final'),
            ])
            ->visible(
                fn(Propiedad $record) =>
                $record->precio_calculado &&
                    !$record->precio_aprobado &&
                    self::tieneAprobacionPendiente($record)
            )
            ->action(function (Propiedad $record, array $data) {
                $user = Auth::user();
                $tipoAprobador = self::getTipoAprobador($user);

                if (!$tipoAprobador) {
                    Notification::make()
                        ->danger()
                        ->title('Sin permisos')
                        ->body('No tienes permisos para rechazar precios.')
                        ->send();
                    return;
                }

                // Buscar la aprobación pendiente
                $aprobacion = $record->aprobacionesPrecio()
                    ->where('tipo_aprobador', $tipoAprobador)
                    ->where('estatus', 'PENDIENTE')
                    ->first();

                if (!$aprobacion) {
                    Notification::make()
                        ->warning()
                        ->title('No hay aprobación pendiente')
                        ->send();
                    return;
                }

                // Rechazar con precio alternativo
                $precioAlternativo = floatval(str_replace(',', '', $data['precio_sugerido_alternativo']));

                $aprobacion->rechazar(
                    userId: $user->id,
                    comentarios: $data['comentarios'],
                    precioSugeridoAlternativo: $precioAlternativo
                );

                // Marcar que requiere decisión de DGE
                $record->marcarRequiereDecisionDGE();

                Notification::make()
                    ->warning()
                    ->title('❌ Precio Rechazado')
                    ->body("Has rechazado el precio. DGE tomará la decisión final.")
                    ->send();

                // Notificar a DGE
                self::notificarDGE($record, $tipoAprobador, $precioAlternativo, $data['comentarios']);
            });
    }

    /**
     * Verificar si el usuario tiene aprobación pendiente
     */
    protected static function tieneAprobacionPendiente(Propiedad $record): bool
    {
        $user = Auth::user();

        $tipoAprobador = self::getTipoAprobador($user);

        if (!$tipoAprobador) {
            return false;
        }

        return $record->aprobacionesPrecio()
            ->where('tipo_aprobador', $tipoAprobador)
            ->where('estatus', 'PENDIENTE')
            ->exists();
    }

    /**
     * Obtener tipo de aprobador según permisos
     */
    protected static function getTipoAprobador($user): ?string
    {
        if ($user->can('precios_aprobar_comercial')) {
            return 'COMERCIAL';
        }

        if ($user->can('precios_aprobar_contabilidad')) {
            return 'CONTABILIDAD';
        }

        return null;
    }

    /**
     * Notificar a DGE que hay un precio que requiere decisión
     */
    protected static function notificarDGE(Propiedad $record, string $areaRechazo, float $precioSugerido, string $comentarios): void
    {
        // Obtener todos los usuarios con permiso de decisión final
        $usuariosDGE = \App\Models\User::permission('precios_decision_final')->get();

        if ($usuariosDGE->isEmpty()) {
            Log::warning("No hay usuarios con permiso 'precios_decision_final' para notificar", [
                'propiedad_id' => $record->id,
            ]);
            return;
        }

        // Enviar notificación a cada usuario DGE
        foreach ($usuariosDGE as $usuario) {
            Notification::make()
                ->title('Precio rechazado - Requiere tu decisión')
                ->body(
                    "{$areaRechazo} rechazó el precio de cotización.\n\n" .
                        "Precio original: $" . number_format($record->precio_venta_sugerido, 2) . "\n" .
                        "Precio sugerido por {$areaRechazo}: $" . number_format($precioSugerido, 2) . "\n\n" .
                        "Comentario: {$comentarios}"
                )
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('warning')
                ->actions([
                    Action::make('ver_propiedad')
                        ->label('Ver Propiedad')
                        ->url(
                            PropiedadResource::getUrl('view', ['record' => $record]),
                            shouldOpenInNewTab: false
                        )
                        ->button()
                        ->markAsRead(),

                    Action::make('ver_widget')
                        ->label('Ir a Decisiones')
                        ->url('/comercial')
                        ->button()
                        ->color('warning')
                        ->markAsRead(),
                ])
                ->persistent() // No se cierra automáticamente
                ->sendToDatabase($usuario); // Guardar en base de datos
        }

        // Log adicional
        Log::info('Notificación DGE enviada', [
            'propiedad_id' => $record->id,
            'numero_credito' => $record->numero_credito,
            'area_rechazo' => $areaRechazo,
            'usuarios_notificados' => $usuariosDGE->pluck('id')->toArray(),
        ]);
    }
}
