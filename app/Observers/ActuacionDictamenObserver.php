<?php

namespace App\Observers;

use App\Enums\EstatusDictamenEnum;
use App\Models\ActuacionDictamen;
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
}
