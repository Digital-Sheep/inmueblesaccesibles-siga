<?php

namespace App\Observers;

use App\Filament\Resources\Juridico\SeguimientoJuicios\SeguimientoJuicioResource;
use App\Models\ActuacionJuicio;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class ActuacionJuicioObserver
{
    /**
     * Al crear una actuación:
     * 1. Auto-genera semana_label si no viene lleno
     * 2. Actualiza ultima_actuacion_at en el seguimiento
     * 3. Si viene etapa_actual, la propaga al seguimiento
     */
    public function created(ActuacionJuicio $actuacion): void
    {
        // 1. Auto-generar semana_label basado en la fecha de la actuación
        if (empty($actuacion->semana_label)) {
            $actuacion->semana_label = 'SEMANA ' . Carbon::parse($actuacion->fecha_actuacion)->format('d/m/Y');
            $actuacion->saveQuietly(); // saveQuietly evita disparar el Observer de nuevo
        }

        // 2 y 3. Actualizar el seguimiento padre
        $seguimiento = $actuacion->seguimientoJuicio;

        if (! $seguimiento) {
            return;
        }

        $datos = [
            'ultima_actuacion_at' => now(),
        ];

        // Solo propaga etapa_actual si viene con contenido
        if (! empty($actuacion->etapa_actual)) {
            $datos['etapa_actual'] = $actuacion->etapa_actual;
        }

        $seguimiento->updateQuietly($datos);

        $this->notificarActuacion(
            titulo: 'Nueva actuación en juicio',
            cuerpo: "{$seguimiento->titulo} — {$actuacion->descripcion_actuacion}",
            url: SeguimientoJuicioResource::getUrl('view', ['record' => $seguimiento->id]),
        );
    }

    /**
     * Al actualizar una actuación:
     * Si cambió etapa_actual, propagar al seguimiento.
     */
    public function updated(ActuacionJuicio $actuacion): void
    {
        if (! $actuacion->wasChanged('etapa_actual')) {
            return;
        }

        $seguimiento = $actuacion->seguimientoJuicio;

        if (! $seguimiento || empty($actuacion->etapa_actual)) {
            return;
        }

        $seguimiento->updateQuietly([
            'etapa_actual' => $actuacion->etapa_actual,
        ]);
    }

    private function notificarActuacion(string $titulo, string $cuerpo, string $url): void
    {
        $destinatarios = User::role(['DGE', 'Direccion_Comercial', 'GRS_Nacional', 'RAC_Atencion_Cliente', 'GAD_Administracion'])->get();

        if ($destinatarios->isEmpty()) {
            return;
        }

        foreach ($destinatarios as $usuario) {
            Notification::make()
                ->title($titulo)
                ->body($cuerpo)
                ->icon('heroicon-o-scale')
                ->info()
                ->actions([
                    \Filament\Actions\Action::make('ver')
                        ->label('Ver seguimiento')
                        ->url($url)
                        ->button()
                        ->markAsRead(),
                ])
                ->sendToDatabase($usuario);
        }
    }
}
