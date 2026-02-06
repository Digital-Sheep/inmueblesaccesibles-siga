<?php

namespace App\Filament\Actions;

use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ValidarYPublicarPropiedadAction
{
    public static function make(): Action
    {
        return Action::make('validar_publicar')
            ->label('✅ Validar y Publicar')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Validar y Publicar Propiedad')
            ->modalDescription(
                fn(Propiedad $record) =>
                self::getModalDescription($record)
            )
            ->modalIcon('heroicon-o-check-circle')
            ->visible(
                fn(Propiedad $record) =>
                auth()->user()->can('propiedades_validar_publicar') &&
                    $record->estatus_comercial === 'BORRADOR'
            )
            ->disabled(fn(Propiedad $record) => !$record->estaListaParaPublicar())
            ->action(function (Propiedad $record) {
                $record->update([
                    'estatus_comercial' => 'PUBLICADA',
                ]);

                $leyenda = $record->leyenda_precio;

                Notification::make()
                    ->success()
                    ->title('✅ Propiedad Publicada')
                    ->body(
                        $leyenda
                            ? "La propiedad está publicada.\n\n{$leyenda}"
                            : 'La propiedad está publicada y lista para venta.'
                    )
                    ->send();
            });
    }

    protected static function getModalDescription(Propiedad $record): string
    {
        if (!$record->estaListaParaPublicar()) {
            $motivos = [];

            if (!$record->precio_calculado) {
                $motivos[] = '❌ Falta calcular el precio de venta';
            }

            if (!$record->numero_credito) {
                $motivos[] = '❌ Falta número de crédito';
            }

            if (!$record->direccion_completa) {
                $motivos[] = '❌ Falta dirección completa';
            }

            if (!$record->estado_id || !$record->municipio_id) {
                $motivos[] = '❌ Falta estado/municipio';
            }

            if (!$record->precio_lista) {
                $motivos[] = '❌ Falta precio de lista';
            }

            return "⚠️ La propiedad NO está lista para publicarse:\n\n" . implode("\n", $motivos);
        }

        $mensaje = "La propiedad cumple con todos los requisitos y será publicada.\n\n";

        // Agregar info del estado del precio
        $estadoPrecio = $record->estado_precio;

        if ($estadoPrecio === 'APROBADO') {
            $mensaje .= "✅ El precio ha sido aprobado por Comercial y Contabilidad.\n";
            $mensaje .= "✅ La propiedad estará lista para venta inmediata.";
        } elseif ($estadoPrecio === 'PENDIENTE_APROBACION') {
            $mensaje .= "⏳ El precio está pendiente de aprobación.\n";
            $mensaje .= "ℹ️ La propiedad se publicará con una leyenda indicando que el precio está en revisión.";
        } elseif ($estadoPrecio === 'REQUIERE_DECISION_DGE') {
            $mensaje .= "⚠️ El precio requiere decisión final de DGE.\n";
            $mensaje .= "ℹ️ La propiedad se publicará pero el precio final está pendiente.";
        }

        return $mensaje;
    }
}
