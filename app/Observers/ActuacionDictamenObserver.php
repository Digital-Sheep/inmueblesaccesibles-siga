<?php

namespace App\Observers;

use App\Enums\EstatusDictamenEnum;
use App\Enums\TipoProcesoDictamenEnum;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\UCP\SeguimientoDictamenUCPResource;
use App\Filament\Resources\Juridico\SeguimientoDictamenes\URRJ\SeguimientoDictamenURRJResource;
use App\Models\ActuacionDictamen;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class ActuacionDictamenObserver
{
    public function created(ActuacionDictamen $actuacion): void
    {
        // 1. Auto-generar semana_label
        if (empty($actuacion->semana_label)) {
            $actuacion->semana_label = 'SEMANA ' . Carbon::parse($actuacion->fecha_actuacion)->format('d/m/Y');
            $actuacion->saveQuietly();
        }

        $seguimiento = $actuacion->seguimientoDictamen;

        if (! $seguimiento) {
            return;
        }

        $datos = ['ultima_actuacion_at' => now()];

        // 2. Propagar etapa_actual si viene con contenido
        if (! empty($actuacion->etapa_actual)) {
            $datos['etapa_actual'] = $actuacion->etapa_actual;
        }

        $seguimiento->updateQuietly($datos);

        $url = $seguimiento->tipo_proceso === TipoProcesoDictamenEnum::VENTA
            ? SeguimientoDictamenUCPResource::getUrl('view', ['record' => $seguimiento->id])
            : SeguimientoDictamenURRJResource::getUrl('view', ['record' => $seguimiento->id]);

        $this->notificarActuacion(
            titulo: 'Nueva actuación en dictamen',
            cuerpo: "{$seguimiento->titulo} — {$actuacion->descripcion_actuacion}",
            url: $url,
        );
    }

    public function updated(ActuacionDictamen $actuacion): void
    {
        if (! $actuacion->wasChanged('etapa_actual')) {
            return;
        }

        $seguimiento = $actuacion->seguimientoDictamen;

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
