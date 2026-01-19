<?php

namespace App\Filament\Widgets;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\EventoAgenda;
use App\Models\Interaccion;
use App\Models\Prospecto;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Grid;

class AgendaComercialWidget extends FullCalendarWidget
{
    protected static ?int $sort = 3;
    public Model|string|null $model = EventoAgenda::class;

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
    public function onEventClick(array $event): void
    {
        $fullId = $event['id'] ?? '';
        $parts  = explode('_', $fullId);

        if (count($parts) < 2) return;

        $tipo   = $parts[0];
        $idReal = $parts[1];

        if ($tipo === 'agenda') {
            $this->mountAction('editAgenda', ['record' => $idReal]);
        } elseif ($tipo === 'tarea') {
            $this->mountAction('editTarea', ['record' => $idReal]);
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
    protected function getActions(): array
    {
        return [
            // --- MODAL A: Editar Cita de Agenda ---
            EditAction::make('editAgenda')
                ->model(EventoAgenda::class)
                ->modalHeading('Editar Cita de Agenda')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('titulo')->required()->columnSpanFull(),
                        DateTimePicker::make('fecha_inicio')->required(),
                        DateTimePicker::make('fecha_fin')->required(),
                        Select::make('tipo')
                            ->options(['CITA_VISITA' => 'Visita', 'REUNION' => 'Reunión'])
                            ->required(),
                        Textarea::make('descripcion')->columnSpanFull(),
                    ])
                ])
                ->footerActions([
                    DeleteAction::make()->model(EventoAgenda::class) // Permitir borrar cita
                ]),

            // --- MODAL B: Gestionar Tarea (Interacción) ---
            EditAction::make('editTarea')
                ->model(Interaccion::class)
                ->modalHeading('Gestionar Tarea Pendiente')
                ->color('warning')
                ->schema([
                    Grid::make(1)->schema([
                        TextInput::make('titulo')
                            ->disabled() // El título no se cambia aquí, viene del CRM
                            ->label('Asunto'),

                        DateTimePicker::make('fecha_programada')
                            ->label('Reprogramar Fecha')
                            ->required(),

                        Textarea::make('comentario')
                            ->label('Resultados / Notas')
                            ->required(),

                        Select::make('estatus')
                            ->options([
                                'PENDIENTE' => 'Pendiente',
                                'COMPLETADA' => 'Completada (Cerrar)',
                                'CANCELADA' => 'Cancelada',
                            ])
                            ->default('PENDIENTE')
                            ->required(),
                    ])
                ])
                // Al guardar, si se marca completada, desaparece del calendario (porque filtramos por pendiente)
                ->after(function () {
                    $this->refreshEvents();
                }),
        ];
    }

    public function config(): array
    {
        return [
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'initialView' => 'timeGridWeek', // Vista semanal es mejor para agenda operativa
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '21:00:00',
            'locale' => 'es',
            'allDaySlot' => false,
        ];
    }
}
