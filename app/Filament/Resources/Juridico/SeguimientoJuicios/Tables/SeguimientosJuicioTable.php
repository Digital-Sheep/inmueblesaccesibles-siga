<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Tables;

use App\Enums\EstatusAvanceEnum;
use App\Enums\NivelPrioridadJuicioEnum;
use App\Enums\SedeJuicioEnum;
use App\Enums\TipoProcesoJuicioEnum;
use App\Filament\Resources\Juridico\SeguimientoJuicios\SeguimientoJuicioResource;
use App\Models\ActuacionJuicio;
use App\Models\SeguimientoJuicio;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SeguimientosJuicioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Alerta visual — ícono rojo si >7 días sin actuación
                IconColumn::make('id')
                    ->label('')
                    ->icon(fn($record) => $record->esta_rezagado ? 'heroicon-s-exclamation-circle' : null)
                    ->color('danger')
                    ->tooltip(fn($record) => $record->esta_rezagado ? 'Sin actuación en más de 7 días' : null)
                    ->width('40px'),

                TextColumn::make('nivel_prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->sortable(),

                TextColumn::make('id_garantia')
                    ->label('ID Garantía')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                TextColumn::make('numero_credito')
                    ->label('Núm. Crédito')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('nombre_cliente')
                    ->label('Cliente')
                    ->searchable()
                    ->default('Sin cliente')
                    ->limit(25),

                TextColumn::make('sede')
                    ->label('Sede')
                    ->badge()
                    ->sortable(),

                TextColumn::make('abogado_nombre')
                    ->label('Abogado')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),

                TextColumn::make('etapa_actual')
                    ->label('Etapa Actual')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->etapa_actual)
                    ->toggleable(),

                TextColumn::make('actuaciones_count')
                    ->label('Actuaciones')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                // Días sin actuación — ordenable, color según urgencia
                TextColumn::make('ultima_actuacion_at')
                    ->label('Días sin actuación')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->ultima_actuacion_at) {
                            return 'Sin actuaciones';
                        }

                        $dias = $record->dias_sin_actuacion;

                        return $dias === 0 ? 'Hoy' : "Hace {$dias} día(s)";
                    })
                    ->color(fn($record) => $record->esta_rezagado ? 'danger' : 'success'),

                IconColumn::make('sin_demanda')
                    ->label('Sin Demanda')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Últ. actualización')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sede')
                    ->label('Sede')
                    ->options(SedeJuicioEnum::class)
                    ->multiple(),

                SelectFilter::make('nivel_prioridad')
                    ->label('Prioridad')
                    ->options(NivelPrioridadJuicioEnum::class)
                    ->multiple(),

                SelectFilter::make('tipo_proceso')
                    ->label('Tipo de Proceso')
                    ->options(TipoProcesoJuicioEnum::class),

                TernaryFilter::make('sin_demanda')
                    ->label('Sin Demanda'),

                TernaryFilter::make('activo')
                    ->label('Solo Activos')
                    ->default(true),
            ])
            ->recordActions([
                ViewAction::make()->label('Ver')
                    ->button()
                    ->color('primary'),
                // Ver actuaciones — modal lectura rápida
                Action::make('ver_actuaciones')
                    ->label('Ver actuaciones')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->button()
                    ->modalHeading(fn($record) => 'Actuaciones — ' . $record->titulo)
                    ->modalDescription(
                        fn($record) => $record->etapa_actual
                            ? "Etapa actual: {$record->etapa_actual}"
                            : null
                    )
                    ->modalContent(function ($record) {
                        $actuaciones = $record->actuaciones()->latest('fecha_actuacion')->limit(5)->get();

                        if ($actuaciones->isEmpty()) {
                            return view('filament.modals.sin-actuaciones');
                        }

                        return view('filament.modals.actuaciones-rapidas', [
                            'actuaciones' => $actuaciones,
                            'verTodoUrl'  => SeguimientoJuicioResource::getUrl('view', ['record' => $record]),
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                ActionGroup::make([
                    // Nueva actuación rápida desde tabla
                    Action::make('nueva_actuacion')
                        ->label('Nueva Actuación')
                        ->icon('heroicon-o-plus-circle')
                        ->color('primary')
                        ->visible(function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->can('seguimientojuicios_editar');
                        })
                        ->schema(fn($record) => self::formActuacionRapida($record))
                        ->action(function ($record, array $data) {
                            ActuacionJuicio::create([
                                'seguimiento_juicio_id' => $record->id,
                                'fecha_actuacion'       => $data['fecha_actuacion'],
                                'descripcion_actuacion' => $data['descripcion_actuacion'],
                                'etapa_actual'          => $data['etapa_actual'] ?? null,
                                'hubo_avance'           => $data['hubo_avance'],
                                'archivo_evidencia'     => $data['archivo_evidencia'] ?? null,
                                // semana_label la genera el Observer automáticamente
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Actuación registrada')
                                ->send();
                        }),

                    EditAction::make()->label('Editar'),

                    // Archivar con confirmación
                    Action::make('archivar')
                        ->label('Archivar')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('¿Archivar este juicio?')
                        ->modalDescription('El juicio dejará de aparecer en los filtros por defecto. Puedes reactivarlo desde edición.')
                        ->modalSubmitActionLabel('Sí, archivar')
                        ->visible(
                            function ($record) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $record->activo && $user->can('seguimientojuicios_editar');
                            }
                        )
                        ->action(fn($record) => $record->update(['activo' => false])),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('ultima_actuacion_at', 'asc')
            ->emptyStateHeading('Sin juicios registrados')
            ->emptyStateDescription('Agrega el primer seguimiento de juicio.')
            ->emptyStateIcon('heroicon-o-scale');
    }

    // ── Form de actuación rápida ───────────────────────────────────────────────

    public static function formActuacionRapida(SeguimientoJuicio $record): array
    {
        return [
            DatePicker::make('fecha_actuacion')
                ->label('Fecha de Actuación')
                ->required()
                ->default(now())
                ->native(false),

            Textarea::make('descripcion_actuacion')
                ->label('Descripción')
                ->required()
                ->rows(3)
                ->helperText('Registra la evidencia o avance del caso (último acuerdo, boletín, etc.)'),

            Textarea::make('etapa_actual')
                ->label('¿Cambia la etapa procesal? (opcional)')
                ->rows(2)
                ->helperText('Si se llena, actualiza automáticamente la etapa del seguimiento'),

            Select::make('hubo_avance')
                ->label('¿Hubo avance?')
                ->options(EstatusAvanceEnum::class)
                ->required(),

            FileUpload::make('archivo_evidencia')
                ->label('Evidencia (opcional)')
                ->disk('private')
                ->directory(fn ($record) => ActuacionJuicio::directorioParaJuicio(
                    $record->id_garantia ?? 'sin-garantia-' . $record->id
                ))
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(10240)
                ->preserveFilenames(false)
                ->helperText('PDF o imagen. Máx. 10MB.')
                ->nullable(),
        ];
    }
}
