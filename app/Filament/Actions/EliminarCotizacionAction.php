<?php

namespace App\Filament\Actions;

use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EliminarCotizacionAction
{
    public static function make(): Action
    {
        return Action::make('eliminar_cotizacion')
            ->label('Eliminar Cotización')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->visible(
                fn(Propiedad $record) =>
                in_array($record->estatus_comercial, ['EN_REVISION', 'DISPONIBLE']) &&
                    auth()->user()->can('propiedades_eliminar_cotizacion')
            )
            ->requiresConfirmation()
            ->modalHeading('¿Eliminar Cotización?')
            ->modalDescription('Esto eliminará la cotización actual, todas las aprobaciones y regresará la propiedad a estado BORRADOR. Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Sí, eliminar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (Propiedad $record) {
                try {
                    DB::transaction(function () use ($record) {
                        // 1. Eliminar cotizaciones
                        $record->cotizaciones()->delete();

                        // 2. Eliminar aprobaciones de precio
                        $record->aprobacionesPrecio()->delete();

                        // 3. Limpiar campos de precio en la propiedad
                        $record->update([
                            'estatus_comercial' => 'BORRADOR',
                            'precio_calculado' => false,
                            'precio_sin_remodelacion' => null,
                            'precio_venta_sugerido' => null,
                            'precio_venta_con_descuento' => null,
                            'precio_aprobado' => false,
                            'precio_fecha_aprobacion' => null,
                            'precio_requiere_decision_dge' => false,
                        ]);
                    });

                    Notification::make()
                        ->success()
                        ->title('✅ Cotización Eliminada')
                        ->body('La cotización ha sido eliminada y la propiedad regresó a estado BORRADOR.')
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('❌ Error al Eliminar')
                        ->body('No se pudo eliminar la cotización: ' . $e->getMessage())
                        ->persistent()
                        ->send();
                }
            });
    }
}
