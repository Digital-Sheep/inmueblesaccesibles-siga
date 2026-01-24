<?php

namespace App\Filament\Widgets;

use App\Models\Interaccion;
use App\Models\Prospecto;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MisProspectosPendientesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['SVT_Asesor', 'GRS_Nacional', 'Direccion_Comercial', 'SVT_Gerente_Regional', 'DGE', 'Super_Admin']);
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        // Verificar alcance
        $puedeVerSucursal = $user->can('interacciones_ver_sucursal_completa') ||
            $user->hasAnyRole(['SVT_Gerente_Regional', 'GRS_Nacional']);

        $query = Interaccion::query()
            ->where('tipo', 'SEGUIMIENTO')
            ->where('estatus', 'PENDIENTE')
            ->whereDate('fecha_programada', '<=', now()->addDays(3))
            ->where('entidad_type', 'App\\Models\\Prospecto'); // Solo prospectos

        if ($puedeVerSucursal) {
            // Ver todos los seguimientos de la sucursal a través del prospecto
            $query->whereHas('entidad', function ($q) use ($user) {
                $q->where('sucursal_id', $user->sucursal_id);
            });
        } else {
            // Solo ver los propios
            $query->where('usuario_id', $user->id);
        }

        $query->orderBy('fecha_programada', 'asc');

        return $table
            ->heading($puedeVerSucursal ? '⏰ Seguimientos Pendientes de la Sucursal' : '⏰ Mis Seguimientos Pendientes')
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('entidad.nombre_completo')
                    ->label('Prospecto')
                    ->searchable()
                    ->sortable(),

                // Agregar columna de responsable si es vista de sucursal
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Responsable')
                    ->searchable()
                    ->visible($puedeVerSucursal),

                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha Programada')
                    ->date('d/M/Y')
                    ->sortable()
                    ->color(fn($record) => $record->fecha_programada < now() ? 'danger' : 'warning')
                    ->icon(fn($record) => $record->fecha_programada < now() ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-clock'),

                Tables\Columns\TextColumn::make('tipo_seguimiento')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'LLAMADA' => '📞 Llamada',
                        'WHATSAPP' => '📱 WhatsApp',
                        'VISITA' => '🏠 Visita',
                        'CORREO' => '📧 Correo',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('comentario')
                    ->label('Notas')
                    ->limit(50)
                    ->wrap(),
            ])
            ->defaultPaginationPageOption(5)
            ->actions([
                Action::make('completar')
                    ->label('Marcar como Hecho')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->action(fn($record) => $record->update(['estatus' => 'COMPLETADA']))
                    ->requiresConfirmation(),
            ]);
    }
}
