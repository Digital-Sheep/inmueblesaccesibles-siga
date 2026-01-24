<?php

namespace App\Filament\Resources\Comercial\Prospectos\Tables;

use App\Models\Prospecto;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Illuminate\Support\Facades\Auth;

class ProspectosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('nombre_completo')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Prospecto $record) => $record->email),

                TextColumn::make('celular')
                    ->url(fn(string $state) => "tel:{$state}")
                    ->searchable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'NUEVO' => 'info',
                        'CONTACTADO' => 'warning',
                        'CITA' => 'primary',
                        'APARTADO' => 'success',
                        'CLIENTE' => 'success',
                        'DESCARTADO' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('ultimo_contacto')
                    ->label('Última Actividad')
                    ->getStateUsing(fn($record) => $record->interacciones()->latest('fecha_realizada')->first()?->resumen_interaccion ?? 'Sin actividad')
                    ->limit(30)
                    ->tooltip(fn(TextColumn $column) => $column->getState())
                    ->color('gray'),

                TextColumn::make('responsable.name')
                    ->label('Asesor')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sucursal.nombre')
                    ->label('Sucursal')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/M/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->options([
                        'NUEVO' => 'Nuevos',
                        'CONTACTADO' => 'En seguimiento',
                        'CITA' => 'Con cita',
                        'APARTADO' => 'En proceso de apartado',
                        'DESCARTADO' => 'Descartados',
                    ])
                    ->native(false),

                SelectFilter::make('usuario_responsable_id')
                    ->relationship('responsable', 'name')
                    ->label('Por asesor')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->can(['prospectos_ver_todos', 'prospectos_ver_sucursal_completa']);
                    })
                    ->native(false),

                SelectFilter::make('sucursal_id')
                    ->relationship('sucursal', 'nombre')
                    ->label('Por sucursal')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->can('prospectos_ver_todos');
                    })
                    ->native(false),
            ])
            ->recordAction(ViewAction::class)
            ->recordActions([
                Action::make('seguimiento')
                    ->label('Actividad')
                    ->tooltip('Registrar Actividad / Seguimiento')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->button()
                    ->modalHeading('Registrar Actividad Realizada')
                    ->modalWidth('md')
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título Breve')
                            ->placeholder('Ej: Llamada de seguimiento, Visita rápida...')
                            ->required()
                            ->maxLength(50),

                        Grid::make(2)->schema([
                            Select::make('tipo')
                                ->options([
                                    'LLAMADA' => '📞 Llamada',
                                    'WHATSAPP' => '📱 WhatsApp',
                                    'CORREO' => '📧 Correo',
                                    'VISITA_SUCURSAL' => '🏢 Visita Sucursal',
                                    'VISITA_PROPIEDAD' => '🏠 Visita Propiedad',
                                    'NOTA_INTERNA' => '📝 Nota Interna',
                                ])
                                ->required()
                                ->native(false),

                            Select::make('resultado')
                                ->options([
                                    'CONTACTADO' => '✅ Contactado',
                                    'BUZON' => '📭 Buzón / No contestó',
                                    'CITA_AGENDADA' => '📅 Cita Agendada',
                                    'LLAMAR_MAS_TARDE' => '⏰ Llamar más tarde',
                                    'NO_INTERESA' => '❌ No interesa',
                                    'DATOS_INCORRECTOS' => '🚫 Datos incorrectos',
                                ])
                                ->required()
                                ->native(false),
                        ]),

                        Textarea::make('comentario')
                            ->label('Detalles / Notas')
                            ->rows(3)
                            ->required(),

                        FileUpload::make('evidencia')
                            ->label('Evidencia (Captura, Foto, PDF)')
                            ->multiple()
                            ->directory('evidencias_prospectos')
                            ->visibility('private')
                            ->imagePreviewHeight('100')
                            ->columnSpanFull(),
                    ])
                    ->action(function (Prospecto $record, array $data) {
                        $record->interacciones()->create([
                            'titulo' => $data['titulo'],
                            'tipo' => $data['tipo'],
                            'resultado' => $data['resultado'],
                            'comentario' => $data['comentario'],
                            'evidencia' => $data['evidencia'],
                            'estatus' => 'COMPLETADA',
                            'fecha_programada' => now(),
                            'fecha_realizada' => now(),
                            'usuario_id' => Auth::id(),
                        ]);

                        // Actualizar estatus del prospecto automáticamente
                        if ($record->estatus === 'NUEVO') {
                            $record->update(['estatus' => 'CONTACTADO']);
                        }

                        // Si el resultado es descartado, podríamos actualizarlo también
                        if ($data['resultado'] === 'NO_INTERESA') {
                            // Opcional: preguntar o hacerlo auto
                            // $record->update(['estatus' => 'DESCARTADO']);
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Actividad registrada')
                            ->send();
                    })
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->can('interacciones_crear');
                    }),
                ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver expediente y timeline')
                    ->modalHeading(fn($record) => "Expediente: {$record->nombre_completo}")
                    ->slideOver()
                    ->modalWidth('5xl')
                    ->button()
                    ->color('gray')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->can('prospectos_ver');
                    }),
                EditAction::make()
                    ->label('Editar')
                    ->tooltip('Corregir datos')
                    ->color('info')
                    ->button()
                    ->slideOver()
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->can('prospectos_editar');
                    }),
                DeleteAction::make()
                    ->button()
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->can('prospectos_eliminar');
                    })
                    ->hidden(fn($record) => $record->estatus === 'CLIENTE'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();
                            return $user->can('prospectos_eliminar');
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
