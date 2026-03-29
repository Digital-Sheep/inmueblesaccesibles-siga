<?php

namespace App\Observers;

use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use App\Models\ActuacionNotaria;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class ActuacionNotariaObserver
{
    public function created(ActuacionNotaria $actuacion): void
    {
        if (empty($actuacion->semana_label)) {
            $actuacion->semana_label = 'SEMANA ' . Carbon::parse($actuacion->fecha_actuacion)->format('d/m/Y');
            $actuacion->saveQuietly();
        }

        $seguimiento = $actuacion->seguimientoNotaria;

        if (! $seguimiento) {
            return;
        }

        $datos = [
            'ultima_actuacion_at' => now(),
        ];

        if (! empty($actuacion->etapa_actual)) {
            $datos['etapa_actual'] = $actuacion->etapa_actual;
        }

        $seguimiento->updateQuietly($datos);

        $this->notificarDGE(
            titulo: 'Nueva actuación en notaría',
            cuerpo: "{$seguimiento->titulo} — {$actuacion->descripcion_actuacion}",
            url: SeguimientoNotariaResource::getUrl('view', ['record' => $seguimiento->id]),
        );
    }

    public function updated(ActuacionNotaria $actuacion): void
    {
        if (! $actuacion->wasChanged('etapa_actual')) {
            return;
        }

        $seguimiento = $actuacion->seguimientoNotaria;

        if (! $seguimiento || empty($actuacion->etapa_actual)) {
            return;
        }

        $seguimiento->updateQuietly([
            'etapa_actual' => $actuacion->etapa_actual,
        ]);
    }

    private function notificarDGE(string $titulo, string $cuerpo, string $url): void
    {
        $destinatarios = User::role('DGE')->get();

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
