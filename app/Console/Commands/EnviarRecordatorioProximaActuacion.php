<?php

namespace App\Console\Commands;

use App\Models\ActuacionJuicio;
use App\Models\ActuacionNotaria;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarRecordatorioProximaActuacion extends Command
{
    protected $signature   = 'juridico:recordatorio-proxima-actuacion';
    protected $description = 'Notifica a los abogados y UCP cuando fecha_proxima_actuacion es hoy o mañana. Se ejecuta diariamente. No reemplaza el recordatorio semanal.';

    public function handle(): int
    {
        $hoy    = now()->toDateString();
        $manana = now()->addDay()->toDateString();

        // ── Juicios ────────────────────────────────────────────────────────────
        $actuacionesJuicio = ActuacionJuicio::with([
            'seguimientoJuicio.abogados',
        ])
            ->whereIn('fecha_proxima_actuacion', [$hoy, $manana])
            ->get();

        foreach ($actuacionesJuicio as $actuacion) {
            $seguimiento = $actuacion->seguimientoJuicio;

            if (! $seguimiento) {
                continue;
            }

            $esMañana = $actuacion->fecha_proxima_actuacion->toDateString() === $manana;
            $cuando   = $esMañana ? 'mañana' : 'hoy';
            $titulo   = "📅 Actuación programada para {$cuando}";
            $cuerpo   = "Juicio: {$seguimiento->titulo}. Fecha: {$actuacion->fecha_proxima_actuacion->format('d/m/Y')}.";

            // Notificar a los abogados asignados al juicio
            foreach ($seguimiento->abogados as $abogado) {
                Notification::make()
                    ->title($titulo)
                    ->body($cuerpo)
                    ->icon('heroicon-o-scale')
                    ->warning()
                    ->sendToDatabase($abogado);
            }

            // También notificar a usuarios con permiso de ver todos (UCP, DGE)
            $supervisores = \App\Models\User::permission('juridico_seguimiento_juicios_ver_todos')->get();
            foreach ($supervisores as $supervisor) {
                Notification::make()
                    ->title($titulo)
                    ->body($cuerpo)
                    ->icon('heroicon-o-scale')
                    ->warning()
                    ->sendToDatabase($supervisor);
            }
        }

        // ── Notarías ───────────────────────────────────────────────────────────
        $actuacionesNotaria = ActuacionNotaria::with('seguimientoNotaria')
            ->whereIn('fecha_proxima_actuacion', [$hoy, $manana])
            ->get();

        foreach ($actuacionesNotaria as $actuacion) {
            $seguimiento = $actuacion->seguimientoNotaria;

            if (! $seguimiento) {
                continue;
            }

            $esMañana = $actuacion->fecha_proxima_actuacion->toDateString() === $manana;
            $cuando   = $esMañana ? 'mañana' : 'hoy';

            $supervisores = \App\Models\User::permission('juridico_seguimiento_notarias_ver_todos')->get();
            foreach ($supervisores as $supervisor) {
                Notification::make()
                    ->title("📄 Actuación de notaría programada para {$cuando}")
                    ->body("Notaría: {$seguimiento->titulo}. Fecha: {$actuacion->fecha_proxima_actuacion->format('d/m/Y')}.")
                    ->icon('heroicon-o-document-text')
                    ->warning()
                    ->sendToDatabase($supervisor);
            }
        }

        $total = $actuacionesJuicio->count() + $actuacionesNotaria->count();
        $this->info("Recordatorios enviados para {$total} actuación(es) programada(s).");

        Log::info('[RecordatorioProximaActuacion] Ejecución completada', [
            'juicios'  => $actuacionesJuicio->count(),
            'notarias' => $actuacionesNotaria->count(),
        ]);

        return self::SUCCESS;
    }
}
