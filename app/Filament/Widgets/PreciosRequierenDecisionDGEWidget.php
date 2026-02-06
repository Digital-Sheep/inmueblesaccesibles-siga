<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
            ->heading('💰 Precios que requieren tu decisión final')
            ->description('Estas propiedades tienen conflictos de aprobación y requieren tu decisión como DGE')
            ->query(
                Propiedad::query()
                    ->where('precio_requiere_decision_dge', true)
                    ->with(['cotizacionActiva', 'sucursal', 'municipio', 'aprobacionesPrecio'])
                    ->orderBy('updated_at', 'desc')
            )
            ->columns([
                TextColumn::make('numero_credito')
                    ->label('No. Crédito')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('direccion_completa')
                    ->label('Dirección')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('precio_venta_sugerido')
                    ->label('Precio Sugerido')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('aprobaciones_status')
                    ->label('Estado Aprobaciones')
                    ->formatStateUsing(function (Propiedad $record) {
                        $comercial = $record->aprobacionesPrecio
                            ->firstWhere('tipo_aprobador', 'COMERCIAL');
                        $contabilidad = $record->aprobacionesPrecio
                            ->firstWhere('tipo_aprobador', 'CONTABILIDAD');

                        $statusComercial = $comercial ? $comercial->estatus : 'N/A';
                        $statusContabilidad = $contabilidad ? $contabilidad->estatus : 'N/A';

                        return "C: {$statusComercial} | CO: {$statusContabilidad}";
                    })
                    ->badge()
                    ->color('warning'),

                TextColumn::make('precios_sugeridos')
                    ->label('Precios Alternativos')
                    ->formatStateUsing(function (Propiedad $record) {
                        $alternativas = $record->aprobacionesPrecio
                            ->whereNotNull('precio_sugerido_alternativo')
                            ->pluck('precio_sugerido_alternativo')
                            ->map(fn($p) => '$' . number_format($p, 0))
                            ->join(' / ');

                        return $alternativas ?: 'Sin sugerencias';
                    })
                    ->color('info'),

                TextColumn::make('updated_at')
                    ->label('Última Act.')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->recordActions([
                Action::make('decidir')
                    ->label('Tomar Decisión')
                    ->icon('heroicon-o-scale')
                    ->color('warning')
                    ->url(
                        fn(Propiedad $record) =>
                        PropiedadResource::getUrl('index')
                    ),
            ])
            ->emptyStateHeading('✅ Sin decisiones pendientes')
            ->emptyStateDescription('No hay precios que requieran tu decisión')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
