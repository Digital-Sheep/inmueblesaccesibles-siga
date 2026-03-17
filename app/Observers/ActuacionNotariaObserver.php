<?php

namespace App\Observers;

use App\Models\ActuacionNotaria;
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
}
