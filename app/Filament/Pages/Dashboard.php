<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DictamenesJuridicosWidget;
use App\Filament\Widgets\DesempenoEquipoWidget;
use App\Filament\Widgets\MisProspectosPendientesWidget;
use App\Filament\Widgets\MisVentasStatsWidget;
use App\Filament\Widgets\PagosPorValidarWidget;
use App\Filament\Widgets\PipelineVentasWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Configuración del grid del dashboard
     * 3 columnas para desktop, más responsive
     */
    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }

    /**
     * Widgets que se mostrarán en el dashboard
     * Cada widget tiene su propio canView() para determinar
     * si el usuario actual puede verlo según su rol
     */
    public function getWidgets(): array
    {
        return [
            // COMERCIAL (Ejecutivos y Gerentes)
            MisVentasStatsWidget::class,              // Stats personales o de sucursal
            MisProspectosPendientesWidget::class,     // Tabla de seguimientos
            PipelineVentasWidget::class,              // Gráfica de pipeline (Gerentes)
            DesempenoEquipoWidget::class,             // Ranking de asesores (Gerentes)

            // JURÍDICO
            DictamenesJuridicosWidget::class,         // Stats de dictámenes

            // CONTABILIDAD/FINANZAS
            PagosPorValidarWidget::class,             // Tabla de pagos pendientes
        ];
    }

    public function getHeading(): string
    {
        $hour = now()->hour;

        $greeting = match (true) {
            $hour < 12 => '☀️ Buenos días',
            $hour < 19 => '🌤️ Buenas tardes',
            default => '🌙 Buenas noches',
        };

        return $greeting . ', ' . auth()->user()->name;
    }

    public function getSubheading(): ?string
    {
        $user = auth()->user();
        $role = $user->roles->first()?->name ?? 'Usuario';
        $sucursal = $user->sucursal?->nombre ?? 'Sin sucursal';

        return "Rol: {$role} | Sucursal: {$sucursal} | " . now()->translatedFormat('l, d \d\e F \d\e Y');
    }
}
