<?php

namespace App\Filament\Widgets;

use Livewire\Attributes\On;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\EventoAgenda;
use App\Models\Interaccion;
use App\Models\Prospecto;

use Filament\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AgendaComercialWidget extends FullCalendarWidget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?int $sort = 3;
    public Model|string|null $model = EventoAgenda::class;

    public Model|int|string|null $record = null;

    public $eventoIdSeleccionado = null;

    /**
     * 1. OBTENER EVENTOS (MÉTODO HÍBRIDO)
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $eventos = [];

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. DEFINIR SI ES SUPERVISOR/ADMIN
        // Ajusta el nombre del rol a como lo tengas en tu BD ('Super_Admin', 'Director_Comercial', etc.)
        $esAdmin = $user->hasRole('Super_Admin') || $user->can('ver_toda_la_agenda');

        // ---------------------------------------------------------
        // A. CITAS DE AGENDA (AZULES)
        // ---------------------------------------------------------
        $queryAgendas = EventoAgenda::query()
            ->where('fecha_inicio', '>=', $fetchInfo['start'])
            ->where('fecha_fin', '<=', $fetchInfo['end']);

        // APLICAR FILTRO SOLO SI NO ES ADMIN
        if (! $esAdmin) {
            $queryAgendas->where('usuario_id', $user->id);
        }

        $agendas = $queryAgendas->with('usuario')->get(); // Traemos 'usuario' para ver de quién es

        foreach ($agendas as $agenda) {
            // Si soy admin, quiero ver de quién es la cita en el título
            $titulo = $esAdmin
                ? "{$agenda->titulo} ({$agenda->usuario->name})"
                : $agenda->titulo;

            $eventos[] = [
                'id'              => 'agenda_' . $agenda->id,
                'title'           => $titulo,
                'start'           => $agenda->fecha_inicio,
                'end'             => $agenda->fecha_fin,
                'backgroundColor' => '#3b82f6',
                'borderColor'     => '#2563eb',
                'extendedProps'   => [
                    'tipo_origen' => 'agenda',
                    'descripcion' => $agenda->descripcion
                ]
            ];
        }

        // ---------------------------------------------------------
        // B. TAREAS PENDIENTES (NARANJAS)
        // ---------------------------------------------------------
        $queryPendientes = Interaccion::query()
            ->where('estatus', '!=', 'COMPLETADA')
            ->whereNotNull('fecha_programada')
            ->whereBetween('fecha_programada', [$fetchInfo['start'], $fetchInfo['end']])
            ->with(['entidad', 'usuario']); // Cargamos usuario también

        // APLICAR FILTRO SOLO SI NO ES ADMIN
        if (! $esAdmin) {
            $queryPendientes->where('usuario_id', $user->id);
        }

        $pendientes = $queryPendientes->get();

        foreach ($pendientes as $tarea) {
            $cliente = $tarea->entidad->nombre_completo
                ?? $tarea->entidad->name
                ?? 'Prospecto';

            // Para el admin, mostramos quién es el responsable de la tarea
            $responsable = $esAdmin ? " - {$tarea->usuario->name}" : "";

            $eventos[] = [
                'id'              => 'tarea_' . $tarea->id,
                'title'           => "📞 {$tarea->tipo}: {$cliente}{$responsable}",
                'start'           => $tarea->fecha_programada,
                'end'             => Carbon::parse($tarea->fecha_programada)->addMinutes(30),
                'backgroundColor' => '#f59e0b',
                'borderColor'     => '#d97706',
                'extendedProps'   => [
                    'tipo_origen' => 'tarea'
                ]
            ];
        }

        return $eventos;
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('titulo')
                    ->label('Título de la Cita')
                    ->required()
                    ->columnSpanFull(),

                Select::make('tipo')
                    ->options([
                        'CITA_VISITA' => 'Visita a Propiedad',
                        'FIRMA_CONTRATO' => 'Firma de Contrato',
                        'REUNION_INTERNA' => 'Reunión Interna',
                    ])
                    ->required(),

                Select::make('participante_id')
                    ->label('Prospecto / Cliente')
                    ->options(Prospecto::all()->pluck('nombre_completo', 'id'))
                    ->searchable()
                    ->preload(),

                Hidden::make('participante_type')
                    ->default(Prospecto::class),

                DateTimePicker::make('fecha_inicio')->required(),
                DateTimePicker::make('fecha_fin')
                    ->required()
                    ->after('fecha_inicio'),

                Textarea::make('descripcion')
                    ->rows(3)
                    ->columnSpanFull(),

                Hidden::make('usuario_id')
                    ->default(Auth::id()),
            ]),
        ];
    }

    /**
     * 3. MANEJAR CLIC EN EVENTO
     */
    // public function onEventClick(array $event): void
    // {
    //     Notification::make()
    //         ->title('Click Recibido')
    //         ->body('ID Crudo: ' . ($event['id'] ?? 'Nulo'))
    //         ->warning() // Color amarillo para distinguir
    //         ->send();

    //     $fullId = $event['id'] ?? '';
    //     $parts  = explode('_', $fullId);

    //     if (count($parts) < 2) {
    //         Notification::make()->title('ID Inválido')->danger()->send();
    //         return;
    //     }

    //     $tipo   = $parts[0];
    //     $idReal = $parts[1];

    //     // if ($tipo === 'agenda') {
    //     //     Action::make('editAgenda')
    //     //         ->action(fn() => Notification::make()->title('Cita de Agenda Seleccionada')->success()->send());
    //     // } elseif ($tipo === 'tarea') {
    //     //     Action::make('editTarea')
    //     //         ->action(fn() => Notification::make()->title('Tarea Pendiente Seleccionada')->success()->send());
    //     // } else {
    //     //     Notification::make()->title('Tipo de Evento Desconocido')->danger()->send();
    //     // }
    //     if ($tipo === 'agenda') {
    //         $this->mountAction('editAgenda', ['record' => $idReal]);
    //     } elseif ($tipo === 'tarea') {
    //         $this->mountAction('editTarea', ['record' => $idReal]);
    //     }
    // }

    public function onEventClick(array $event): void
    {
        $parts = explode('_', $event['id'] ?? '');
        if (count($parts) < 2) return;

        $tipo = $parts[0];
        $idReal = $parts[1];

        if ($tipo === 'agenda') {
            $this->mountAction('editAgenda', ['record_id' => $idReal]);
        } elseif ($tipo === 'tarea') {
            $this->mountAction('editTarea', ['record_id' => $idReal]);
        }
    }

    /**
     * 4. DRAG & DROP (ACTUALIZACIÓN DE FECHAS)
     */
    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        $fullId = $event['id'] ?? '';
        $parts  = explode('_', $fullId);

        if (count($parts) < 2) return true; // Revertir si falla

        $tipo   = $parts[0];
        $idReal = $parts[1];

        // Convertimos fechas considerando zona horaria
        $newStart = Carbon::parse($event['start'])->setTimezone(config('app.timezone'));
        $newEnd   = isset($event['end']) ? Carbon::parse($event['end'])->setTimezone(config('app.timezone')) : null;

        if ($tipo === 'agenda') {
            $cita = EventoAgenda::find($idReal);
            if ($cita) {
                $cita->update([
                    'fecha_inicio' => $newStart,
                    'fecha_fin'    => $newEnd ?? $cita->fecha_fin, // Si no hay fin, mantenemos duración o calculamos
                ]);
                return false; // Éxito (no revertir)
            }
        } elseif ($tipo === 'tarea') {
            $tarea = Interaccion::find($idReal);
            if ($tarea) {
                $tarea->update([
                    'fecha_programada' => $newStart,
                ]);
                return false; // Éxito
            }
        }

        return true; // Revertir si no encontró registro
    }

    /**
     * 5. DEFINICIÓN DE ACCIONES (MODALES DE EDICIÓN)
     */
    // protected function getActions(): array
    // {
    //     return [
    //         // --- MODAL A: Editar Cita de Agenda ---
    //         Action::make('editAgenda')
    //             ->modalWidth('lg')
    //             ->modalHeading('Editar Cita de Agenda')
    //             ->mountUsing(function (Form $form, array $arguments) {
    //                 $recordId = $arguments['record'] ?? null;

    //                 if ($recordId && $cita = EventoAgenda::find($recordId)) {
    //                     $form->fill([
    //                         'titulo' => $cita->titulo,
    //                         'fecha_inicio' => $cita->fecha_inicio,
    //                         'fecha_fin' => $cita->fecha_fin,
    //                         'tipo' => $cita->tipo,
    //                         'descripcion' => $cita->descripcion,
    //                     ]);
    //                 }
    //             })
    //             ->record(fn(array $arguments) => EventoAgenda::find($arguments['record'] ?? null))
    //             ->schema([
    //                 Grid::make(2)->schema([
    //                     TextInput::make('titulo')->required()->columnSpanFull(),
    //                     DateTimePicker::make('fecha_inicio')->required(),
    //                     DateTimePicker::make('fecha_fin')->required(),
    //                     Select::make('tipo')
    //                         ->options(['CITA_VISITA' => 'Visita', 'REUNION' => 'Reunión'])
    //                         ->required(),
    //                     Textarea::make('descripcion')->columnSpanFull(),
    //                 ])
    //             ])
    //             ->action(function (array $data, array $arguments) {
    //                 $record = EventoAgenda::find($arguments['record']);

    //                 if ($record) {
    //                     $record->update($data);
    //                     Notification::make()->title('Cita actualizada')->success()->send();
    //                     $this->refreshEvents();
    //                 }
    //             })
    //             ->modalFooterActions([
    //                 DeleteAction::make('delete')
    //                     ->requiresConfirmation()
    //                     ->action(function (array $arguments) {
    //                         $record = EventoAgenda::find($arguments['record']);
    //                         if ($record) {
    //                             $record->delete();
    //                             Notification::make()->title('Cita eliminada')->success()->send();
    //                         }
    //                     })
    //                     ->after(fn() => $this->refreshEvents())
    //             ]),

    //         // --- MODAL B: Gestionar Tarea (Interacción) ---
    //         Action::make('editTarea')
    //             ->modalHeading('Gestionar Tarea Pendiente')
    //             ->color('warning')
    //             ->mountUsing(function (Form $form, array $arguments) {
    //                 $recordId = $arguments['record'] ?? null;

    //                 if ($recordId && $tarea = Interaccion::find($recordId)) {
    //                     $form->fill([
    //                         'titulo' => $tarea->titulo,
    //                         'fecha_programada' => $tarea->fecha_programada,
    //                         'comentario' => $tarea->comentario,
    //                         'estatus' => $tarea->estatus,
    //                     ]);
    //                 }
    //             })
    //             ->schema([
    //                 Grid::make(1)->schema([
    //                     TextInput::make('titulo')
    //                         ->disabled()
    //                         ->label('Asunto'),

    //                     DateTimePicker::make('fecha_programada')
    //                         ->label('Reprogramar Fecha')
    //                         ->required(),

    //                     Textarea::make('comentario')
    //                         ->label('Resultados / Notas')
    //                         ->required(),

    //                     Select::make('estatus')
    //                         ->options([
    //                             'PENDIENTE' => 'Pendiente',
    //                             'COMPLETADA' => 'Completada (Cerrar)',
    //                             'CANCELADA' => 'Cancelada',
    //                         ])
    //                         ->default('PENDIENTE')
    //                         ->required(),
    //                 ])
    //             ])
    //             ->action(function (array $data, array $arguments) {
    //                 $record = Interaccion::find($arguments['record']);
    //                 if ($record) {
    //                     $record->update($data);
    //                     Notification::make()->title('Tarea actualizada')->success()->send();
    //                     $this->refreshEvents();
    //                 }
    //             }),
    //     ];
    // }

    // public function getActions(): array
    // {
    //     return [
    //         Action::make('testAction')
    //             ->modalHeading('¡Éxito! El modal funciona')
    //             ->form([
    //                 TextInput::make('mensaje')
    //                     ->label('Mensaje del Sistema')
    //                     ->default('Si lees esto, los modales funcionan.')
    //                     ->disabled(),

    //                 TextInput::make('id_evento')
    //                     ->label('ID recibido del calendario')
    //                     // Recuperamos el argumento pasado desde onEventClick
    //                     ->default(fn(array $arguments) => $arguments['id_recibido'] ?? 'Nada'),
    //             ])
    //             ->action(function () {
    //                 Notification::make()->title('Acción ejecutada')->success()->send();
    //             })
    //             ->cancelParentActions(), // Buena práctica para evitar conflictos
    //     ];
    // }

    public function editAgendaAction(): Action
    {
        return Action::make('editAgenda')
            ->modalHeading('Editar Cita de Agenda')
            ->modalSubmitActionLabel('Guardar Cambios')
            ->color('warning')
            ->mountUsing(function ($form, array $arguments) {
                $this->eventoIdSeleccionado = $arguments['record_id'] ?? null;

                $cita = EventoAgenda::find($arguments['record_id'] ?? null);
                if ($cita) {
                    $form->fill([
                        'titulo' => $cita->titulo,
                        'tipo' => $cita->tipo,
                        'fecha_inicio' => $cita->fecha_inicio,
                        'fecha_fin' => $cita->fecha_fin,
                    ]);
                }
            })
            ->schema([
                TextInput::make('titulo')->required(),
                Select::make('tipo')
                    ->options(['CITA_VISITA' => 'Visita', 'FIRMA_CONTRATO' => 'Firma', 'REUNION_INTERNA' => 'Reunión']),
                Grid::make()->schema([
                    DateTimePicker::make('fecha_inicio')->required(),
                    DateTimePicker::make('fecha_fin')->required(),
                ])
            ])
            ->action(function (array $data, array $arguments) {
                $cita = EventoAgenda::find($arguments['record_id']);
                $cita?->update($data);
                Notification::make()->title('Cita guardada')->success()->send();
            })
            ->after(function () {
                return redirect(request()->header('Referer'));
            })
            ->modalFooterActions([
                Action::make('guardar')
                    ->label('Guardar Cambios')
                    ->color('primary')
                    ->submit('editAgenda'),
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->color('gray')
                    ->close(),
                Action::make('borrar')
                    ->extraAttributes(['style' => 'margin-left: auto'])
                    ->label('Eliminar Cita')
                    ->color('danger') // Color rojo
                    ->icon('heroicon-m-trash') // Icono de basura
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar cita?')
                    ->modalDescription('Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->action(function (array $arguments) {
                        $id = $this->eventoIdSeleccionado;

                        if ($id) {
                            EventoAgenda::find($id)?->delete();

                            Notification::make()
                                ->title('Cita eliminada')
                                ->success()
                                ->send();
                        }

                        Notification::make()
                            ->title('Cita eliminada: ID ' . ($id ?? 'N/A'))
                            ->success()
                            ->send();
                    })
                    ->after(
                        function () {
                            return redirect(request()->header('Referer'));
                        }
                    ),
            ]);
    }

    public function editTareaAction(): Action
    {
        return Action::make('editTarea')
            ->modalHeading('Gestionar Tarea')
            ->modalSubmitActionLabel('Guardar Cambios')
            ->color('primary')
            ->mountUsing(function ($form, array $arguments) {
                $tarea = Interaccion::find($arguments['record_id'] ?? null);
                if ($tarea) {
                    $form->fill([
                        'titulo' => $tarea->titulo,
                        'fecha_programada' => $tarea->fecha_programada,
                        'estatus' => $tarea->estatus,
                        'observaciones' => $tarea->observaciones ?? '', // Asegura que no sea null
                    ]);
                }
            })
            ->schema([
                TextInput::make('titulo')->disabled(),
                DateTimePicker::make('fecha_programada')->required(),
                Select::make('estatus')
                    ->options(['PENDIENTE' => 'Pendiente', 'COMPLETADA' => 'Completada', 'CANCELADA' => 'Cancelada'])
                    ->required(),
                Textarea::make('observaciones')
            ])
            ->action(function (array $data, array $arguments) {
                $tarea = Interaccion::find($arguments['record_id']);
                $tarea?->update($data);
                Notification::make()->title('Tarea actualizada')->success()->send();
            })
            ->after(function () {
                return redirect(request()->header('Referer'));
            });
    }

    public function config(): array
    {
        return [
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'initialView' => 'timeGridWeek', // Vista semanal
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '21:00:00',
            'locale' => 'es',
            'allDaySlot' => false,
        ];
    }

    #[On('filament-fullcalendar:refresh')]
    public function refreshRecords(): void
    {
        // Al ejecutarse esto, Livewire sabe que algo cambió
        // y renderiza de nuevo el componente.
    }
}
