<?php

namespace App\Filament\Widgets;

use App\Models\ProcesoVenta;
use App\Models\Prospecto;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PipelineVentasWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['SVT_Gerente_Regional', 'GRS_Nacional', 'Direccion_Comercial', 'DGE', 'Super_Admin']);
    }

    protected function getData(): array
    {
        $user = Auth::user();

        // Verificar si es nivel corporativo o de sucursal
        $esNivelCorporativo = $user->hasAnyRole(['DGE', 'Direccion_Comercial', 'GRS_Nacional', 'Super_Admin']);

        $query = ProcesoVenta::query()
            ->select('estatus', DB::raw('count(*) as total'))
            ->whereNotIn('estatus', ['CANCELADO', 'ENTREGADO']);

        // Si es Gerente de Sucursal, filtrar por su sucursal
        if (!$esNivelCorporativo) {
            $query->whereHas('vendedor', fn($q) => $q->where('sucursal_id', $user->sucursal_id));
        }

        $data = $query->groupBy('estatus')->get();

        $etapas = [
            'ACTIVO' => 'Negociación',
            'VISITA_REALIZADA' => 'Visita Realizada',
            'APARTADO_VALIDADO' => 'Apartado',
            'EN_DICTAMINACION' => 'Dictaminación',
            'DICTAMINADO_POSITIVO' => 'Dictaminado',
            'ENGANCHE_PAGADO' => 'Enganche',
            'EN_PROCESO_COMPRA' => 'En Compra',
            'LIQUIDACION_PAGADA' => 'Liquidación',
            'ESCRITURADO' => 'Escriturado',
        ];

        $labels = [];
        $valores = [];

        foreach ($etapas as $key => $label) {
            $total = $data->where('estatus', $key)->first()?->total ?? 0;
            if ($total > 0) {
                $labels[] = $label;
                $valores[] = $total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Procesos Activos',
                    'data' => $valores,
                    'backgroundColor' => [
                        '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b',
                        '#10b981', '#06b6d4', '#6366f1', '#84cc16', '#14b8a6'
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        return 'Pipeline de Ventas';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
