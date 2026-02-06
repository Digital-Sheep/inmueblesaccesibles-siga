<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\AprobarPrecioAction;
use App\Filament\Actions\RechazarPrecioAction;
use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PreciosPendientesContabilidadWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('precios_aprobar_contabilidad');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('💰 Precios pendientes de aprobación (Contabilidad)')
            ->description('Estas propiedades requieren tu validación del precio')
            ->query(
                Propiedad::query()
                    ->whereHas('aprobacionesPrecio', function ($query) {
                        $query->where('tipo_aprobador', 'CONTABILIDAD')
                            ->where('estatus', 'PENDIENTE');
                    })
                    ->with(['cotizacionActiva', 'sucursal', 'municipio'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('numero_credito')
                    ->label('No. Crédito')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('direccion_completa')
                    ->label('Dirección')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->sortable(),

                TextColumn::make('precio_lista')
                    ->label('Precio Lista')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('cotizacionActiva.total_costos')
                    ->label('Total Costos')
                    ->money('MXN')
                    ->color('danger'),

                TextColumn::make('precio_venta_con_descuento')
                    ->label('Precio Final')
                    ->money('MXN')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('porcentaje_utilidad')
                    ->label('% Utilidad')
                    ->suffix('%')
                    ->color('warning')
                    ->weight('bold'),

                TextColumn::make('cotizacionActiva.created_at')
                    ->label('Calculado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Revisar')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(
                        fn(Propiedad $record) =>
                        PropiedadResource::getUrl('index')
                    ),

                AprobarPrecioAction::make(),

                RechazarPrecioAction::make(),
            ])
            ->emptyStateHeading('✅ Sin precios pendientes')
            ->emptyStateDescription('No hay precios esperando tu aprobación')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
