<?php

namespace App\Observers;

use App\Models\Dictamen;
use App\Models\ProcesoVenta;
use Filament\Actions\Action;
use Filament\Notifications\Notification;


class DictamenObserver
{
    public function updated(Dictamen $dictamen): void
    {
        // Solo actuamos si el estatus cambió a TERMINADO y tiene un veredicto
        if ($dictamen->isDirty('estatus') && $dictamen->estatus === 'TERMINADO') {

            $proceso = $dictamen->procesoVenta;
            if (! $proceso) return;

            // Lógica de Negocio según Resultado
            switch ($dictamen->resultado_final) {
                case 'POSITIVO':
                    // Avanza a la etapa de Enganche / Contrato Final
                    $proceso->update([
                        'estatus' => 'DICTAMINADO_R2', // R2 = Positivo en tu nomenclatura
                    ]);

                    // Notificar al Vendedor
                    Notification::make()
                        ->success()
                        ->title('Dictamen Positivo (R2)')
                        ->body("La propiedad **{$dictamen->propiedad->direccion_completa}** ha sido aprobada. Puedes proceder al enganche.")
                        ->sendToDatabase($proceso->vendedor);
                    break;

                case 'NEGATIVO':
                    // Regresa a revisión o se cancela (según tu regla exacta)
                    // Aquí asumimos que se marca para cambio o se detiene
                    Notification::make()
                        ->danger()
                        ->title('Dictamen Negativo (R1)')
                        ->body("La propiedad no es viable. Revisa las observaciones de Jurídico.")
                        ->sendToDatabase($proceso->vendedor)
                        ->actions([
                            Action::make('ver_detalle')
                                ->button()
                                ->url(route('filament.admin.resources.comercial.proceso-ventas.view', $proceso))
                        ])
                        ->send();
                    break;

                case 'CAMBIO':
                    $proceso->update([
                        'estatus' => 'CAMBIO_PROPIEDAD',
                    ]);
                    break;
            }
        }
    }
}
