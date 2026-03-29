<?php

namespace App\Console\Commands;

use App\Models\SeguimientoJuicio;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarRecordatorioSeguimientoJuicios extends Command
{
    protected $signature   = 'juridico:recordatorio-juicios';
    protected $description = 'Envía recordatorio semanal a UCP para actualizar seguimientos de juicios (ejecutar cada lunes)';

    public function handle(): int
    {
        // Contar juicios activos que requieren seguimiento
        $totalActivos = SeguimientoJuicio::where('activo', true)->count();

        if ($totalActivos === 0) {
            $this->info('No hay juicios activos — no se envió notificación.');
            return self::SUCCESS;
        }

        // Usuarios con permiso de editar seguimientos (UCP, DGE, etc.)
        $destinatarios = User::permission('juridico_seguimiento_juicios_editar')->get();

        if ($destinatarios->isEmpty()) {
            Log::warning('[RecordatorioJuicios] No se encontraron usuarios con permiso juridico_seguimiento_juicios_editar');
            return self::SUCCESS;
        }

        foreach ($destinatarios as $usuario) {
            Notification::make()
                ->title('Recordatorio: Seguimiento Semanal de Juicios')
                ->body("Hay {$totalActivos} juicios activos que requieren actualización esta semana.")
                ->icon('heroicon-o-scale')
                ->warning()
                ->sendToDatabase($usuario);
        }

        $this->info("Recordatorio enviado a {$destinatarios->count()} usuario(s). Juicios activos: {$totalActivos}.");

        Log::info('[RecordatorioJuicios] Recordatorio semanal enviado', [
            'destinatarios' => $destinatarios->pluck('name')->toArray(),
            'juicios_activos' => $totalActivos,
        ]);

        return self::SUCCESS;
    }
}
