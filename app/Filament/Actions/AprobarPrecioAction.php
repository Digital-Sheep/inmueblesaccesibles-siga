<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\AprobacionPrecio;
use App\Models\Propiedad;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AprobarPrecioAction
{
    public static function make(): Action
    {
        return Action::make('aprobar_precio')
            ->label('✅ Aprobar Precio')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Aprobar Precio de Venta')
            ->modalDescription(
                fn(Propiedad $record) =>
                "Precio sugerido: $" . number_format($record->precio_venta_sugerido, 2) . "\n" .
                    "Precio con descuento: $" . number_format($record->precio_venta_con_descuento, 2)
            )
            ->modalIcon('heroicon-o-check-badge')
            ->modalWidth('lg')
            ->schema([
                Textarea::make('comentarios')
                    ->label('Comentarios (Opcional)')
                    ->placeholder('Ej: El precio está dentro del rango aceptable para esta zona...')
                    ->rows(3)
                    ->maxLength(500),
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
                        ->body('No tienes permisos para aprobar precios.')
                        ->send();
                    return;
                }

                // Buscar la aprobación pendiente del usuario actual
                $aprobacion = $record->aprobacionesPrecio()
                    ->where('tipo_aprobador', $tipoAprobador)
                    ->where('estatus', 'PENDIENTE')
                    ->first();

                if (!$aprobacion) {
                    Notification::make()
                        ->warning()
                        ->title('No hay aprobación pendiente')
                        ->body('Esta propiedad no tiene una aprobación pendiente para tu área.')
                        ->send();
                    return;
                }

                // Aprobar
                $aprobacion->aprobar($user->id, $data['comentarios'] ?? null);

                // Verificar si todas las aprobaciones están completas
                self::verificarAprobacionesCompletas($record);

                Notification::make()
                    ->success()
                    ->title('✅ Precio Aprobado')
                    ->body("Has aprobado el precio como {$tipoAprobador}.")
                    ->send();
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
     * Verificar si todas las aprobaciones están completas
     */
    protected static function verificarAprobacionesCompletas(Propiedad $record): void
    {
        $aprobaciones = $record->aprobacionesPrecio;

        $todasAprobadas = $aprobaciones->every(fn($a) => $a->estatus === 'APROBADO');
        $hayRechazos = $aprobaciones->contains(fn($a) => $a->estatus === 'RECHAZADO');

        if ($todasAprobadas) {
            // Todas aprobaron → Marcar precio como aprobado
            $record->marcarPrecioComoAprobado();

            Notification::make()
                ->success()
                ->title('🎉 Precio Completamente Aprobado')
                ->body('Todas las áreas han aprobado el precio. La propiedad puede ser publicada.')
                ->send();

            self::notificarCalculador($record);
        } elseif ($hayRechazos) {
            // Hay rechazos → Requiere decisión de DGE
            $record->marcarRequiereDecisionDGE();

            // Notificar a DGE
            self::notificarDGE($record);
        } else {
            // Solo una área aprobó, falta la otra
            self::notificarAreaPendiente($record);
        }
    }

    /**
     * Notificar a quien calculó el precio que fue aprobado
     */
    protected static function notificarCalculador(Propiedad $record): void
    {
        $cotizacion = $record->cotizacionActiva;

        if (!$cotizacion || !$cotizacion->calculadaPor) {
            return;
        }

        Notification::make()
            ->title('🎉 Precio Aprobado Completamente')
            ->body(sprintf(
                "El precio que calculaste para la propiedad %s fue aprobado por Comercial y Contabilidad.\n\n" .
                    "Precio final: $%s",
                $record->numero_credito,
                number_format($record->precio_venta_con_descuento, 2)
            ))
            ->icon('heroicon-o-check-badge')
            ->iconColor('success')
            ->actions([
                Action::make('ver')
                    ->label('Ver Propiedad')
                    ->url(PropiedadResource::getUrl('view', ['record' => $record]))
                    ->button(),
            ])
            ->sendToDatabase($cotizacion->calculadaPor);
    }

    /**
     * Notificar al área que falta aprobar
     */
    protected static function notificarAreaPendiente(Propiedad $record): void
    {
        // Obtener las aprobaciones
        $aprobaciones = $record->aprobacionesPrecio;

        // Buscar cuál está pendiente
        $pendiente = $aprobaciones->firstWhere('estatus', 'PENDIENTE');

        if (!$pendiente) {
            return;
        }

        // Obtener usuarios del área pendiente
        $permiso = $pendiente->tipo_aprobador === 'COMERCIAL'
            ? 'precios_aprobar_comercial'
            : 'precios_aprobar_contabilidad';

        $usuarios = User::permission($permiso)->get();

        if ($usuarios->isEmpty()) {
            return;
        }

        foreach ($usuarios as $usuario) {
            Notification::make()
                ->title('📋 Precio Pendiente de tu Aprobación')
                ->body(sprintf(
                    "La propiedad %s tiene un precio pendiente de aprobación por tu área.\n\n" .
                        "Una área ya aprobó, falta tu validación.",
                    $record->numero_credito
                ))
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->actions([
                    Action::make('revisar')
                        ->label('Revisar Ahora')
                        ->url(PropiedadResource::getUrl('view', ['record' => $record]))
                        ->button(),
                ])
                ->sendToDatabase($usuario);
        }
    }

    /**
     * Notificar a DGE que hay un precio que requiere decisión
     */
    protected static function notificarDGE(Propiedad $record): void
    {
        $usuariosDGE = User::permission('precios_decision_final')->get();

        if ($usuariosDGE->isEmpty()) {
            Log::warning("No hay usuarios con permiso 'precios_decision_final' para notificar", [
                'propiedad_id' => $record->id,
            ]);
            return;
        }

        foreach ($usuariosDGE as $usuario) {
            Notification::make()
                ->title('⚠️ Precio Rechazado - Requiere tu Decisión')
                ->body(sprintf(
                    "La propiedad %s requiere tu decisión final.\n\n" .
                        "Precio sugerido: $%s\n" .
                        "Estado: Rechazado por una o más áreas",
                    $record->numero_credito,
                    number_format($record->precio_venta_con_descuento, 2)
                ))
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('warning')
                ->actions([
                    Action::make('ver')
                        ->label('Ver Propiedad')
                        ->url(PropiedadResource::getUrl('view', ['record' => $record]))
                        ->button(),
                ])
                ->persistent()
                ->sendToDatabase($usuario);
        }

        Log::info("Precio rechazado - Notificación enviada a DGE", [
            'propiedad_id' => $record->id,
            'numero_credito' => $record->numero_credito,
            'usuarios_notificados' => $usuariosDGE->pluck('id')->toArray(),
        ]);
    }
}
