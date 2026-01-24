<?php

namespace App\Filament\Widgets;

use App\Models\Prospecto;
use App\Models\ProcesoVenta;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MisVentasStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // Solo visible para roles comerciales
    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['SVT_Asesor', 'Direccion_Comercial', 'SVT_Gerente_Regional', 'GRS_Nacional', 'DGE', 'Super_Admin']);
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        // Verificar si puede ver toda la sucursal
        $puedeVerSucursal = $user->can('ventas_ver_sucursal_completa') ||
            $user->hasAnyRole(['SVT_Gerente_Regional', 'Direccion_Comercial', 'GRS_Nacional', 'Super_Admin']);
        // Query base según permisos
        $queryProspectos = Prospecto::query();
        $queryProcesos = ProcesoVenta::query();

        if ($puedeVerSucursal) {
            // Ver toda la sucursal
            $queryProspectos->where('sucursal_id', $user->sucursal_id);
            $queryProcesos->whereHas('vendedor', fn($q) => $q->where('sucursal_id', $user->sucursal_id));
        } else {
            // Solo ver los propios
            $queryProspectos->where('usuario_responsable_id', $user->id);
            $queryProcesos->where('vendedor_id', $user->id);
        }

        // Mis/Nuestros prospectos activos
        $misProspectos = (clone $queryProspectos)
            ->whereIn('estatus', ['NUEVO', 'CONTACTADO', 'INTERESADO'])
            ->count();

        // Mis/Nuestros procesos de venta activos
        $misProcesosActivos = (clone $queryProcesos)
            ->whereNotIn('estatus', ['CANCELADO', 'ENTREGADO'])
            ->count();

        // Ventas cerradas este mes
        $ventasCerradasMes = (clone $queryProcesos)
            ->where('estatus', 'ENTREGADO')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Apartados pendientes de validar
        $apartadosPendientes = (clone $queryProcesos)
            ->whereIn('estatus', ['APARTADO_POR_VALIDAR', 'ENGANCHE_POR_VALIDAR', 'LIQUIDACION_POR_VALIDAR'])
            ->count();

        // Textos dinámicos según alcance
        $prefijo = $puedeVerSucursal ? 'Sucursal' : 'Mis';

        return [
            Stat::make("$prefijo: Prospectos Activos", $misProspectos)
                ->description($puedeVerSucursal ? 'De toda la sucursal' : 'Requieren seguimiento')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart([7, 3, 4, 5, 6, 3, $misProspectos]),

            Stat::make("$prefijo: Procesos en Curso", $misProcesosActivos)
                ->description('En diferentes etapas')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->chart([2, 3, 3, 4, 4, 5, $misProcesosActivos]),

            Stat::make("$prefijo: Ventas del Mes", $ventasCerradasMes)
                ->description('Propiedades entregadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([0, 1, 1, 2, 2, 3, $ventasCerradasMes]),

            Stat::make('Pagos Pendientes', $apartadosPendientes)
                ->description('Por validar en Contabilidad')
                ->descriptionIcon('heroicon-m-clock')
                ->color($apartadosPendientes > 0 ? 'danger' : 'gray')
                ->chart([1, 2, 1, 0, 1, 2, $apartadosPendientes]),
        ];
    }
}
