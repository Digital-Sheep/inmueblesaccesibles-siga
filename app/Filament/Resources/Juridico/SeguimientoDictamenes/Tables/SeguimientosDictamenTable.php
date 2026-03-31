<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\Tables;

use App\Enums\EstatusAvanceEnum;
use App\Enums\EstatusDictamenEnum;
use App\Enums\ResultadoDictamenEnum;
use App\Enums\TipoProcesoDictamenEnum;
use App\Models\ActuacionDictamen;
use App\Models\SeguimientoDictamen;
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

class SeguimientosDictamenTable
{
    public static function configure(Table $table, string $resource): Table

    {
        return $table
            ->columns([
                // Alerta visual — rezagado si >7 días sin actuación
                IconColumn::make('id')
                    ->label('')
                    ->icon(
                        fn($record) => $record->esta_rezagado && $record->estatus === EstatusDictamenEnum::ACTIVO
                            ? 'heroicon-s-exclamation-circle' : null
                    )
                    ->color('danger')
                    ->tooltip(
                        fn($record) => $record->esta_rezagado && $record->estatus === EstatusDictamenEnum::ACTIVO
                            ? 'Sin actuación en más de 7 días' : null
                    )
                    ->width('40px'),

                TextColumn::make('tipo_proceso')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('estatus')
                    ->label('Estatus')
                    ->badge()
                    ->sortable(),

                TextColumn::make('numero_credito')
                    ->label('Núm. Crédito')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('numero_expediente')
                    ->label('Expediente')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('solicitante.name')
                    ->label('Solicitado por')
                    ->default('—')
                    ->limit(20),

                TextColumn::make('dictamen_juridico_resultado')
                    ->label('Jurídico')
                    ->badge()
                    ->color(
                        fn($record) => $record->dictamen_juridico_resultado instanceof ResultadoDictamenEnum
                            ? $record->dictamen_juridico_resultado->getColor() : 'gray'
                    )
                    ->default('—'),

                TextColumn::make('dictamen_registral_resultado')
                    ->label('Registral')
                    ->badge()
                    ->color(
                        fn($record) => $record->dictamen_registral_resultado instanceof ResultadoDictamenEnum
                            ? $record->dictamen_registral_resultado->getColor() : 'gray'
                    )
                    ->default('—'),

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

                TextColumn::make('ultima_actuacion_at')
                    ->label('Días sin actuación')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->estatus === EstatusDictamenEnum::COMPLETADO) {
                            return 'Completado';
                        }
                        $dias = $record->dias_sin_actuacion;
                        return $dias === 0 ? 'Hoy' : "Hace {$dias} día(s)";
                    })
                    ->color(fn($record) => match (true) {
                        $record->estatus === EstatusDictamenEnum::COMPLETADO => 'success',
                        $record->esta_rezagado => 'danger',
                        default => 'success',
                    }),
            ])
            ->filters([
                SelectFilter::make('tipo_proceso')
                    ->label('Tipo de Proceso')
                    ->options(TipoProcesoDictamenEnum::class),

                SelectFilter::make('estatus')
                    ->label('Estatus')
                    ->options(EstatusDictamenEnum::class),

                SelectFilter::make('dictamen_juridico_resultado')
                    ->label('Resultado Jurídico')
                    ->options(ResultadoDictamenEnum::class),

                SelectFilter::make('dictamen_registral_resultado')
                    ->label('Resultado Registral')
                    ->options(ResultadoDictamenEnum::class),

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
                    ->label('Actuaciones')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->button()
                    ->modalHeading(fn($record) => 'Actuaciones — ' . $record->titulo)
                    ->modalDescription(
                        fn($record) => $record->etapa_actual
                            ? "Etapa actual: {$record->etapa_actual}" : null
                    )
                    ->modalContent(function ($record) use ($resource) {
                        $actuaciones = $record->actuaciones()->latest('fecha_actuacion')->limit(5)->get();

                        if ($actuaciones->isEmpty()) {
                            return view('filament.modals.sin-actuaciones');
                        }

                        return view('filament.modals.actuaciones-rapidas', [
                            'actuaciones' => $actuaciones,
                            'verTodoUrl' => $resource::getUrl('view', ['record' => $record]),
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
                        ->visible(
                            function ($record) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $record->estatus !== EstatusDictamenEnum::COMPLETADO
                                    && $user->can('seguimientodictamenes_editar');
                            }
                        )
                        ->schema(fn($record) => self::formActuacionRapida($record))
                        ->action(function ($record, array $data) {
                            ActuacionDictamen::create([
                                'seguimiento_dictamen_id' => $record->id,
                                'fecha_actuacion'         => $data['fecha_actuacion'],
                                'fecha_proxima_actuacion' => $data['fecha_proxima_actuacion'] ?? null,
                                'descripcion_actuacion'   => $data['descripcion_actuacion'],
                                'etapa_actual'            => $data['etapa_actual'] ?? null,
                                'hubo_avance'             => $data['hubo_avance'],
                                'archivo_evidencia'       => $data['archivo_evidencia'] ?? null,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Actuación registrada')
                                ->send();
                        }),


                    EditAction::make()->label('Editar')
                    ->visible(function ($record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('seguimientodictamenes_editar');
                    }),

                    // Marcar como completado
                    Action::make('marcar_completado')
                        ->label('Marcar Completado')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('¿Marcar dictamen como completado?')
                        ->modalDescription('Una vez completado no se podrán agregar más actuaciones.')
                        ->modalSubmitActionLabel('Sí, completar')
                        ->visible(
                            function ($record) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $record->estatus === EstatusDictamenEnum::ACTIVO
                                    && $user->can('seguimientodictamenes_editar');
                            }
                        )
                        ->action(function ($record) {
                            $record->update(['estatus' => EstatusDictamenEnum::COMPLETADO]);
                        }),

                    // Archivar
                    Action::make('archivar')
                        ->label('Archivar')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('¿Archivar este dictamen?')
                        ->modalDescription('Dejará de aparecer en los filtros por defecto.')
                        ->modalSubmitActionLabel('Sí, archivar')
                        ->visible(
                            function ($record) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $record->activo
                                    && $user->can('seguimientodictamenes_editar');
                            }
                        )
                        ->action(function ($record) {
                            $record->update(['activo' => false]);
                        }),
                ]),


            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()
                    ->label('Eliminar seleccionados')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('seguimientodictamenes_eliminar');
                    }),
                ]),
            ])
            ->defaultSort('ultima_actuacion_at', 'asc')
            ->emptyStateHeading('Sin dictámenes registrados')
            ->emptyStateIcon('heroicon-o-document-check');
    }

    public static function formActuacionRapida(SeguimientoDictamen $record): array
    {
        return [
            DatePicker::make('fecha_actuacion')
                ->label('Fecha de Actuación')
                ->required()
                ->default(now())
                ->native(false),

            DatePicker::make('fecha_proxima_actuacion')
                ->label('Próxima Actuación (opcional)')
                ->native(false)
                ->nullable(),

            Textarea::make('descripcion_actuacion')
                ->label('Descripción')
                ->required()
                ->rows(3),

            Textarea::make('etapa_actual')
                ->label('¿Cambia la etapa? (opcional)')
                ->rows(2),

            Select::make('hubo_avance')
                ->label('¿Hubo Avance?')
                ->options(EstatusAvanceEnum::class)
                ->required(),

            FileUpload::make('archivo_evidencia')
                ->label('Evidencia (opcional)')
                ->disk('private')
                ->directory(fn() => ActuacionDictamen::directorioParaDictamen($record->id))
                ->acceptedFileTypes(['application/pdf', 'application/x-pdf', 'application/octet-stream', 'image/jpeg', 'image/png'])
                ->rules(['mimes:pdf,jpg,jpeg,png'])
                ->maxSize(102400)
                ->preserveFilenames(false)
                ->nullable(),
        ];
    }
}
