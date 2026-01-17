<?php

namespace App\Observers;

use App\Models\Interaccion;
use App\Models\ProcesoVenta;

class InteraccionObserver
{
    public function created(Interaccion $interaccion): void
    {
        // Solo nos importan las LLAMADAS para el conteo de abandono
        if ($interaccion->tipo !== 'LLAMADA') {
            return;
        }

        // Verificar si la interacción está ligada a un Proceso de Venta
        // (Ojo: Tu interacción puede estar ligada a Prospecto, hay que llegar al Proceso)

        $proceso = null;

        if ($interaccion->entidad_type === 'App\Models\ProcesoVenta') {
            $proceso = ProcesoVenta::find($interaccion->entidad_id);
        } elseif ($interaccion->entidad_type === 'App\Models\Prospecto') {
            // Si se registró en el Prospecto, buscamos su proceso ACTIVO
            $proceso = ProcesoVenta::where('interesado_type', 'App\Models\Prospecto')
                ->where('interesado_id', $interaccion->entidad_id)
                ->where('estatus', 'ACTIVO')
                ->first();
        }

        if (! $proceso) {
            return;
        }

        // Incrementar el contador según la etapa actual
        if ($proceso->etapa_seguimiento === 'ASESOR') {
            $proceso->increment('intentos_contacto_asesor');

            // Regla de Negocio: Si ya cumplió 2, ¿pasa a Gerente?
            // Por ahora solo contamos. El cambio de etapa suele ser manual o por botón "Escalar".
        } elseif ($proceso->etapa_seguimiento === 'GERENTE_LOCAL') {
            $proceso->increment('intentos_contacto_gerente');
        }
    }
}
