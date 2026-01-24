<?php

namespace App\Filament\Widgets;

use App\Models\ProcesoVenta;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DesempenoEquipoWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['SVT_Gerente_Regional', 'GRS_Nacional', 'DGE', 'Direccion_Comercial', 'Super_Admin']);
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        // Determinar alcance
        $esNivelCorporativo = $user->hasAnyRole(['DGE', 'GRS_Nacional', 'Direccion_Comercial', 'Super_Admin']);

        $query = User::query()
            ->select('users.*')
            ->selectRaw('
                (SELECT COUNT(*) FROM procesos_venta
                 WHERE vendedor_id = users.id
                 AND estatus = "ENTREGADO"
                 AND MONTH(updated_at) = MONTH(CURRENT_DATE)
                 AND YEAR(updated_at) = YEAR(CURRENT_DATE)
                ) as ventas_mes
            ')
            ->selectRaw('
                (SELECT COUNT(*) FROM procesos_venta
                 WHERE vendedor_id = users.id
                 AND estatus NOT IN ("CANCELADO", "ENTREGADO")
                ) as procesos_activos
            ')
            ->selectRaw('
                (SELECT COUNT(*) FROM prospectos
                 WHERE usuario_responsable_id = users.id
                 AND estatus IN ("NUEVO", "CONTACTADO", "INTERESADO")
                ) as prospectos_activos
            ')
            // ->whereHas(
            //     'roles',
            //     fn($q) =>
            //     $q->whereIn('name', ['SVT_Asesor'])
            // )
            ->where('activo', true);

        // Filtrar por sucursal si no es corporativo
        if (!$esNivelCorporativo) {
            $query->where('sucursal_id', $user->sucursal_id);
        }

        $query->orderByDesc('ventas_mes')
            ->orderByDesc('procesos_activos');

        $heading = $esNivelCorporativo
            ? '🏆 Top Asesores del Mes'
            : '🏆 Desempeño del Equipo - ' . $user->sucursal->nombre;

        return $table
            ->heading($heading)
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('ranking')
                    ->label('#')
                    ->state(function ($record, $rowLoop) {
                        $position = $rowLoop->iteration;
                        return match ($position) {
                            1 => '🥇',
                            2 => '🥈',
                            3 => '🥉',
                            default => $position
                        };
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Asesor')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->visible($esNivelCorporativo)
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('ventas_mes')
                    ->label('Ventas del Mes')
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('procesos_activos')
                    ->label('En Proceso')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('prospectos_activos')
                    ->label('Prospectos')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(10);
    }
}
