<?php

namespace App\Observers;

use App\Models\ProcesoVenta;
use App\Models\Propiedad;
use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcesoVentaObserver
{
    /**
     * Estatus que se consideran "proceso terminado" — no cuentan como activos.
     */
    private const ESTATUS_TERMINADOS = ['CANCELADO', 'ENTREGADO'];

    // =========================================================================
    // HOOKS DEL OBSERVER
    // =========================================================================

    /**
     * Al crear un ProcesoVenta:
     * Si la propiedad estaba DISPONIBLE → marcarla EN_INTERES.
     */
    public function created(ProcesoVenta $procesoVenta): void
    {
        $propiedad = $procesoVenta->propiedad;

        if ($propiedad->estatus_comercial === 'DISPONIBLE') {
            $this->marcarEnInteres($propiedad, $procesoVenta);
        }
    }

    /**
     * Antes de actualizar un ProcesoVenta — detectar cambios de estatus relevantes.
     *
     * Usamos `updating` (pre-save) con `isDirty` para reaccionar al cambio.
     * Nota: los sub-updates que este observer dispara sobre procesos perdedores
     * también pasarán por aquí, pero están protegidos por los guards de estatus.
     */
    public function updating(ProcesoVenta $procesoVenta): void
    {
        if (! $procesoVenta->isDirty('estatus')) {
            return;
        }

        $nuevoEstatus = $procesoVenta->estatus;

        // Alguien pagó el apartado → apartar la propiedad y cancelar competidores
        if ($nuevoEstatus === 'APARTADO_VALIDADO') {
            $this->apartarPropiedad($procesoVenta);
            return;
        }

        // Un proceso fue cancelado → verificar si la propiedad debe regresar a DISPONIBLE
        if ($nuevoEstatus === 'CANCELADO') {
            // Usamos `updated` en lugar de aquí para tener el registro ya guardado en BD.
            // Ver método updated() abajo.
        }
    }

    /**
     * Después de actualizar — verificar si hay que regresar la propiedad a DISPONIBLE.
     *
     * Se usa `updated` (post-save) para que el conteo de procesos activos
     * ya incluya el estatus recién guardado de este proceso.
     */
    public function updated(ProcesoVenta $procesoVenta): void
    {
        if (! $procesoVenta->wasChanged('estatus')) {
            return;
        }

        if ($procesoVenta->estatus === 'CANCELADO') {
            // Recargar la propiedad fresca para tener su estatus actual
            $propiedad = $procesoVenta->propiedad()->first();
            $this->verificarSiRegresarADisponible($propiedad);
        }
    }

    // =========================================================================
    // LÓGICA PRIVADA
    // =========================================================================

    /**
     * Marcar la propiedad como EN_INTERES y registrar en log.
     */
    private function marcarEnInteres(Propiedad $propiedad, ProcesoVenta $proceso): void
    {
        $propiedad->update([
            'estatus_comercial' => 'EN_INTERES',
        ]);

        Log::info('[ProcesoVentaObserver] Propiedad marcada EN_INTERES', [
            'propiedad_id'   => $propiedad->id,
            'numero_credito' => $propiedad->numero_credito,
            'proceso_id'     => $proceso->id,
        ]);
    }

    /**
     * Cuando se valida un apartado:
     *  1. Actualiza la propiedad a EN_PROCESO y registra el interesado_principal.
     *  2. Cancela todos los procesos competidores activos.
     *  3. Notifica a cada asesor perdedor.
     *
     * Usa lockForUpdate() para evitar condiciones de carrera si dos
     * asesores validan el apartado casi simultáneamente.
     */
    private function apartarPropiedad(ProcesoVenta $procesoGanador): void
    {
        DB::transaction(function () use ($procesoGanador) {

            // Recargar la propiedad con bloqueo de escritura
            $propiedad = Propiedad::lockForUpdate()->findOrFail($procesoGanador->propiedad_id);

            // Guard: si ya está EN_PROCESO, otro request se adelantó — ignorar
            if ($propiedad->estatus_comercial === 'EN_PROCESO') {
                Log::warning('[ProcesoVentaObserver] Intento de apartar propiedad ya EN_PROCESO', [
                    'propiedad_id' => $propiedad->id,
                    'proceso_id'   => $procesoGanador->id,
                ]);
                return;
            }

            // 1. Actualizar propiedad
            $propiedad->update([
                'estatus_comercial'          => 'EN_PROCESO',
                'interesado_principal_type'  => $procesoGanador->interesado_type,
                'interesado_principal_id'    => $procesoGanador->interesado_id,
            ]);

            // 2. Obtener procesos competidores activos
            $perdedores = $propiedad->procesosVenta()
                ->where('id', '!=', $procesoGanador->id)
                ->whereNotIn('estatus', self::ESTATUS_TERMINADOS)
                ->get();

            if ($perdedores->isEmpty()) {
                Log::info('[ProcesoVentaObserver] Propiedad apartada sin competidores', [
                    'propiedad_id' => $propiedad->id,
                    'proceso_id'   => $procesoGanador->id,
                ]);
                return;
            }

            // 3. Cancelar cada proceso perdedor y notificar al asesor
            foreach ($perdedores as $perdedor) {
                // updateQuietly evita que este observer se vuelva a disparar
                // para los procesos que se cancelan aquí, evitando
                // el riesgo de un loop (aunque los guards ya lo previenen).
                $perdedor->updateQuietly([
                    'estatus'             => 'CANCELADO',
                    'motivo_cancelacion'  => 'Propiedad apartada por otro cliente',
                    'fecha_cancelacion'   => now(),
                ]);

                $this->notificarAsesorCancelacion($perdedor, $propiedad);
            }

            Log::info('[ProcesoVentaObserver] Propiedad apartada — procesos cancelados', [
                'propiedad_id'        => $propiedad->id,
                'numero_credito'      => $propiedad->numero_credito,
                'proceso_ganador_id'  => $procesoGanador->id,
                'procesos_cancelados' => $perdedores->pluck('id')->toArray(),
            ]);
        });
    }

    /**
     * Verifica si todos los procesos de la propiedad terminaron.
     * Si es así y la propiedad está EN_INTERES → regresa a DISPONIBLE.
     *
     * Solo aplica cuando el estatus es EN_INTERES. Si ya está EN_PROCESO
     * o VENDIDA, la lógica de esos flujos es independiente.
     */
    private function verificarSiRegresarADisponible(Propiedad $propiedad): void
    {
        if (! in_array($propiedad->estatus_comercial, ['EN_INTERES', 'EN_PROCESO'])) {
            return;
        }

        $procesosActivos = $propiedad->procesosVenta()
            ->whereNotIn('estatus', self::ESTATUS_TERMINADOS)
            ->count();

        if ($procesosActivos === 0) {
            $propiedad->update([
                'estatus_comercial' => 'DISPONIBLE',
                'interesado_principal_type' => null,
                'interesado_principal_id'   => null,
            ]);

            Log::info('[ProcesoVentaObserver] Propiedad regresó a DISPONIBLE — sin procesos activos', [
                'propiedad_id'   => $propiedad->id,
                'numero_credito' => $propiedad->numero_credito,
            ]);
        }
    }

    /**
     * Envía notificación persistente al asesor cuyo proceso fue cancelado
     * por competencia.
     */
    private function notificarAsesorCancelacion(ProcesoVenta $proceso, Propiedad $propiedad): void
    {
        $asesor = $proceso->vendedor;

        if (! $asesor) {
            Log::warning('[ProcesoVentaObserver] Proceso sin asesor asignado — notificación omitida', [
                'proceso_id' => $proceso->id,
            ]);
            return;
        }

        // Obtener el nombre del interesado con null-safety (lección aprendida de producción)
        $nombreInteresado = 'Cliente desconocido';

        if ($proceso->interesado) {
            $nombreInteresado = match ($proceso->interesado_type) {
                'App\\Models\\Prospecto' => $proceso->interesado->nombre_completo ?? 'Prospecto',
                'App\\Models\\Cliente'  => trim(
                    ($proceso->interesado->nombres ?? '') . ' ' .
                        ($proceso->interesado->apellido_paterno ?? '')
                ) ?: 'Cliente',
                default => 'Interesado',
            };
        }

        Notification::make()
            ->warning()
            ->title('⚠️ Propiedad apartada por otro cliente')
            ->body(
                "La propiedad {$propiedad->numero_credito} que estabas trabajando con " .
                    "{$nombreInteresado} fue apartada por otro cliente.\n\n" .
                    "Dirección: {$propiedad->direccion_completa}\n\n" .
                    "El proceso de venta ha sido cancelado automáticamente."
            )
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->actions([
                Action::make('ver_propiedad')
                    ->label('Ver Propiedad')
                    ->url(
                        PropiedadResource::getUrl('view', ['record' => $propiedad]),
                        shouldOpenInNewTab: false
                    )
                    ->button()
                    ->markAsRead(),

                Action::make('ver_mis_procesos')
                    ->label('Mis Procesos')
                    ->url('/comercial/proceso-ventas')
                    ->button()
                    ->color('gray')
                    ->markAsRead(),
            ])
            ->persistent()
            ->sendToDatabase($asesor);

        Log::info('[ProcesoVentaObserver] Notificación enviada a asesor', [
            'proceso_id'  => $proceso->id,
            'asesor_id'   => $asesor->id,
            'asesor_name' => $asesor->name,
        ]);
    }
}
