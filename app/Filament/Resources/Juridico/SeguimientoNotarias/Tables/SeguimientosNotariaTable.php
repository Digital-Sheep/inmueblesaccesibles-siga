<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Tables;

use App\Enums\EstatusAvanceEnum;
use App\Enums\SedeJuicioEnum;
use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use App\Models\ActuacionNotaria;
use App\Models\SeguimientoNotaria;
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

class SeguimientosNotariaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('id')
                    ->label('')
                    ->icon(fn($record) => $record->esta_rezagado ? 'heroicon-s-exclamation-circle' : null)
                    ->color('danger')
                    ->tooltip(fn($record) => $record->esta_rezagado ? 'Sin actuación en más de 7 días' : null)
                    ->width('40px'),

                TextColumn::make('id_garantia')
                    ->label('ID Garantía')
                    ->searchable()
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

                TextColumn::make('notario')
                    ->label('Notario')
                    ->limit(20)
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
                        if (! $record->ultima_actuacion_at) {
                            return 'Sin actuaciones';
                        }
                        $dias = $record->dias_sin_actuacion;
                        return $dias === 0 ? 'Hoy' : "Hace {$dias} día(s)";
                    })
                    ->color(fn($record) => $record->esta_rezagado ? 'danger' : 'success'),
            ])
            ->filters([
                SelectFilter::make('sede')
                    ->label('Sede')
                    ->options(SedeJuicioEnum::class)
                    ->multiple(),

                TernaryFilter::make('activo')
                    ->label('Solo Activos')
                    ->default(true),
            ])
            ->recordActions([
                ViewAction::make()->label('Ver')
                    ->button()
                    ->color('primary'),
                Action::make('ver_actuaciones')
                    ->label('Actuaciones')
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
                            'verTodoUrl'  => SeguimientoNotariaResource::getUrl('view', ['record' => $record]),
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                ActionGroup::make([
                    Action::make('nueva_actuacion')
                        ->label('Nueva Actuación')
                        ->icon('heroicon-o-plus-circle')
                        ->color('primary')
                        ->visible(function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->can('seguimientonotarias_editar');
                        })
                        ->schema(fn($record) => self::formActuacionRapida($record))
                        ->action(function ($record, array $data) {
                            ActuacionNotaria::create([
                                'seguimiento_notaria_id' => $record->id,
                                'fecha_actuacion'        => $data['fecha_actuacion'],
                                'descripcion_actuacion'  => $data['descripcion_actuacion'],
                                'etapa_actual'           => $data['etapa_actual'] ?? null,
                                'hubo_avance'            => $data['hubo_avance'],
                                'archivo_evidencia'      => $data['archivo_evidencia'] ?? null,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Actuación registrada')
                                ->send();
                        }),


                    EditAction::make()->label('Editar'),

                    Action::make('archivar')
                        ->label('Archivar')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('¿Archivar este seguimiento de notaría?')
                        ->modalDescription('Dejará de aparecer en los filtros por defecto. Puedes reactivarlo desde edición.')
                        ->modalSubmitActionLabel('Sí, archivar')
                        ->visible(
                            function ($record) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $record->activo && $user->can('seguimientonotarias_editar');
                            }
                        )
                        ->action(fn($record) => $record->update(['activo' => false])),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('ultima_actuacion_at', 'asc')
            ->emptyStateHeading('Sin seguimientos de notarías')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function formActuacionRapida(SeguimientoNotaria $record): array
    {
        return [
            DatePicker::make('fecha_actuacion')
                ->label('Fecha')
                ->required()
                ->default(now())
                ->native(false),

            Textarea::make('descripcion_actuacion')
                ->label('Descripción')
                ->required()
                ->rows(3)
                ->helperText('Registra la evidencia o avance del caso (último acuerdo, boletín, etc.)'),

            Textarea::make('etapa_actual')
                ->label('¿Cambia la etapa? (opcional)')
                ->rows(2)
                ->helperText('Si se llena, actualiza automáticamente la etapa del seguimiento'),

            Select::make('hubo_avance')
                ->label('¿Hubo avance?')
                ->options(EstatusAvanceEnum::class)
                ->required(),

            FileUpload::make('archivo_evidencia')
                ->label('Evidencia (opcional)')
                ->disk('private')
                ->directory(fn($record) => ActuacionNotaria::directorioParaNotaria(
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
