<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\DecisionDGEAction;
use App\Models\Propiedad;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PreciosRequierenDecisionDGEWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('precios_decision_final');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚖️ Precios que requieren tu decisión final')
            ->description('Estas garantías tienen conflictos de aprobación y requieren tu decisión como DGE')
            ->query(
                Propiedad::query()
                    ->where('precio_requiere_decision_dge', true)
                    ->with(['cotizacionActiva', 'sucursal', 'municipio', 'aprobacionesPrecio.aprobador'])
                    ->orderBy('updated_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_credito')
                    ->label('No. Crédito')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Copiado!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('direccion_completa')
                    ->label('Dirección')
                    ->limit(30)
                    ->searchable()
                    ->tooltip(fn(Propiedad $record) => $record->direccion_completa),

                Tables\Columns\TextColumn::make('precio_venta_con_descuento')
                    ->label('Precio original')
                    ->money('MXN')
                    ->sortable()
                    ->color('primary')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('aprobaciones_status')
                    ->label('Estado aprobaciones')
                    ->formatStateUsing(function (Propiedad $record) {
                        $comercial = $record->aprobacionesPrecio
                            ->firstWhere('tipo_aprobador', 'COMERCIAL');
                        $contabilidad = $record->aprobacionesPrecio
                            ->firstWhere('tipo_aprobador', 'CONTABILIDAD');

                        $iconComercial = $comercial?->estatus === 'APROBADO' ? '✅' : '❌';
                        $iconContabilidad = $contabilidad?->estatus === 'APROBADO' ? '✅' : '❌';

                        $statusComercial = $comercial?->estatus ?? 'N/A';
                        $statusContabilidad = $contabilidad?->estatus ?? 'N/A';

                        return "{$iconComercial} C: {$statusComercial} | {$iconContabilidad} CO: {$statusContabilidad}";
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('precios_sugeridos')
                    ->label('Precios Alternativos')
                    ->formatStateUsing(function (Propiedad $record) {
                        $alternativas = [];

                        foreach ($record->aprobacionesPrecio as $apr) {
                            if ($apr->precio_sugerido_alternativo) {
                                $tipo = $apr->tipo_aprobador === 'COMERCIAL' ? 'C' : 'CO';
                                $alternativas[] = "{$tipo}: $" . number_format($apr->precio_sugerido_alternativo, 0);
                            }
                        }

                        return !empty($alternativas)
                            ? implode(' | ', $alternativas)
                            : 'Sin sugerencias';
                    })
                    ->color(
                        fn(Propiedad $record) =>
                        $record->aprobacionesPrecio->whereNotNull('precio_sugerido_alternativo')->isNotEmpty()
                            ? 'info'
                            : 'gray'
                    )
                    ->weight(
                        fn(Propiedad $record) =>
                        $record->aprobacionesPrecio->whereNotNull('precio_sugerido_alternativo')->isNotEmpty()
                            ? 'medium'
                            : 'normal'
                    ),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Act.')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn(Propiedad $record) => $record->updated_at->format('d/m/Y H:i:s')),
            ])
            ->recordActions([
                DecisionDGEAction::make(), // ✅ Usar la action completa
            ])
            ->emptyStateHeading('✅ Sin decisiones pendientes')
            ->emptyStateDescription('No hay precios que requieran tu decisión en este momento')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->poll('30s'); // ✅ Auto-refresh cada 30 segundos
    }
}
