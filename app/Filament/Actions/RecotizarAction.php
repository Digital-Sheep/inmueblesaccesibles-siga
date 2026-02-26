<?php

namespace App\Filament\Actions;

use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class RecotizarAction
{
    public static function make(): Action
    {
        return Action::make('recotizar')
            ->label('Recotizar')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->visible(
                fn(Propiedad $record) =>
                $record->precio_calculado &&
                    !$record->precio_aprobado &&
                    auth()->user()->can('propiedades_calcular_precio')
            )
            ->requiresConfirmation()
            ->modalHeading('¿Recotizar Propiedad?')
            ->modalDescription('Esto desactivará la cotización actual y te permitirá crear una nueva. Las aprobaciones pendientes se eliminarán.')
            ->modalSubmitActionLabel('Sí, recotizar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (Propiedad $record) {
                try {
                    DB::transaction(function () use ($record) {
                        // 1. Desactivar cotización actual (mantener historial)
                        $record->cotizaciones()
                            ->where('activa', true)
                            ->update(['activa' => false]);

                        // 2. Eliminar aprobaciones pendientes
                        $record->aprobacionesPrecio()
                            ->whereIn('estatus', ['PENDIENTE', 'RECHAZADO'])
                            ->delete();

                        // 3. Limpiar flags de precio
                        $record->update([
                            'estatus_comercial' => 'BORRADOR',
                            'precio_calculado' => false,
                            'precio_aprobado' => false,
                            'precio_fecha_aprobacion' => null,
                            'precio_requiere_decision_dge' => false,
                        ]);
                    });

                    Notification::make()
                        ->success()
                        ->title('✅ Lista para Recotizar')
                        ->body('La cotización anterior se desactivó. Ahora puedes calcular un nuevo precio.')
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('❌ Error al Recotizar')
                        ->body('No se pudo preparar la recotización: ' . $e->getMessage())
                        ->persistent()
                        ->send();
                }
            });
    }
}
