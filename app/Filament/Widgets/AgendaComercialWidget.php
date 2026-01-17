<?php

namespace App\Filament\Widgets;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\EventoAgenda;
use App\Models\Interaccion;
use Filament\Forms;
use Filament\Actions; // Importar acciones
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Grid;

class AgendaComercialWidget extends FullCalendarWidget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 2;

    protected string $view = 'filament-fullcalendar::fullcalendar';

    /**
     * Eventos del Calendario
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $user = Auth::user();
        $eventos = [];

        // 1. Citas
        $agendas = EventoAgenda::where('usuario_id', $user->id)
            ->where('fecha_inicio', '>=', $fetchInfo['start'])
            ->where('fecha_fin', '<=', $fetchInfo['end'])
            ->get();

        foreach ($agendas as $agenda) {
            $eventos[] = [
                'id' => 'agenda_' . $agenda->id,
                'title' => $agenda->titulo,
                'start' => $agenda->fecha_inicio,
                'end' => $agenda->fecha_fin,
                'backgroundColor' => '#3b82f6',
                'borderColor' => '#1d4ed8',
                'extendedProps' => ['tipo' => 'agenda']
            ];
        }

        // 2. Pendientes
        $pendientes = Interaccion::where('usuario_id', $user->id)
            ->whereNotNull('fecha_programada')
            ->where('fecha_programada', '>=', $fetchInfo['start'])
            ->where('fecha_programada', '<=', $fetchInfo['end'])
            ->with(['entidad'])
            ->get();

        foreach ($pendientes as $tarea) {
            $nombre = $tarea->entidad->nombre_completo ??
                $tarea->entidad->nombre_completo_virtual ??
                'Prospecto';

            $eventos[] = [
                'id' => 'tarea_' . $tarea->id,
                'title' => "📞 {$tarea->tipo}: {$nombre}",
                'start' => $tarea->fecha_programada,
                'backgroundColor' => '#f59e0b',
                'borderColor' => '#d97706',
                'extendedProps' => ['tipo' => 'tarea']
            ];
        }

        return $eventos;
    }

    /**
     * Formulario de creación
     */
    public function getFormSchema(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('titulo')->required(),
                Select::make('tipo')
                    ->options(['CITA' => 'Cita', 'LLAMADA' => 'Llamada'])
                    ->required(),
                DateTimePicker::make('fecha_inicio')->required(),
                DateTimePicker::make('fecha_fin')->required(),
            ]),
        ];
    }

    // Acción de crear
    public function createEvent(array $data): void
    {
        EventoAgenda::create([
            'usuario_id' => Auth::id(),
            'titulo' => $data['titulo'],
            'tipo' => $data['tipo'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
        ]);
        $this->refreshEvents();
    }

    public function config(): array
    {
        return [
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'initialView' => 'dayGridMonth',
            'height' => '800px',
            'contentHeight' => 'auto',
        ];
    }

    /**
     * Actualizar evento al soltar (Drag & Drop)
     * Firma exacta: 6 argumentos.
     */
    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        // 1. Obtener ID del evento movido (viene directo en $event)
        $fullId = $event['id'] ?? '';
        $parts  = explode('_', $fullId);

        // Validación básica
        if (count($parts) < 2) {
            // Devuelve TRUE para revertir el movimiento (que regrese a su lugar original)
            return true;
        }

        $tipo   = $parts[0]; // 'agenda' o 'tarea'
        $idReal = $parts[1]; // ID numérico

        // 2. Obtener nuevas fechas (vienen directo en $event)
        $newStart = $event['start'] ?? null;
        $newEnd   = $event['end'] ?? null;

        // 3. Actualizar Base de Datos

        // CASO A: AGENDA
        if ($tipo === 'agenda') {
            $cita = \App\Models\EventoAgenda::find($idReal);
            if ($cita) {
                $cita->update([
                    'fecha_inicio' => $newStart,
                    'fecha_fin'    => $newEnd ?? $cita->fecha_fin,
                ]);
                // Retornamos FALSE para indicar "No revertir" (aceptar el cambio)
                return false;
            }
        }

        // CASO B: TAREA
        elseif ($tipo === 'tarea') {
            $tarea = \App\Models\Interaccion::find($idReal);
            if ($tarea) {
                $tarea->update([
                    'fecha_programada' => $newStart,
                ]);
                return false;
            }
        }

        // Si algo falló, devolvemos TRUE para que el evento regrese visualmente a su lugar anterior
        return true;
    }

    protected function getActions(): array
    {
        return [
            // -----------------------------------------------------------------
            // 1. EDITAR CITA DE AGENDA
            // -----------------------------------------------------------------
            EditAction::make('editAgenda')
                ->model(EventoAgenda::class)
                ->modalHeading('Editar Cita')
                ->modalWidth('lg') // Hacemos el modal un poco más ancho
                ->form([
                    // Fila 1: Título y Color
                    Grid::make(2)->schema([
                        TextInput::make('titulo')
                            ->label('Asunto de la Cita')
                            ->required()
                            ->columnSpan(1),

                        ColorPicker::make('color')
                            ->label('Color de Etiqueta')
                            ->columnSpan(1),
                    ]),

                    // Fila 2: Fechas (Inicio y Fin)
                    Grid::make(2)->schema([
                        DateTimePicker::make('fecha_inicio')
                            ->label('Inicia')
                            ->seconds(false) // Ocultar segundos para limpieza visual
                            ->required(),

                        DateTimePicker::make('fecha_fin')
                            ->label('Termina')
                            ->seconds(false)
                            ->required()
                            // Regla: Que la fecha fin no sea antes que la inicio
                            ->after('fecha_inicio'),
                    ]),

                    // Fila 3: Relaciones (Prospecto y Vendedor)
                    Grid::make(2)->schema([
                        Select::make('prospecto_id')
                            ->label('Prospecto / Cliente')
                            ->relationship('prospecto', 'nombre_completo') // Ajusta 'nombre_completo' a tu campo real
                            ->searchable() // Vital si tienes muchos prospectos
                            ->preload()
                            ->required(),

                        Select::make('user_id')
                            ->label('Asignado a')
                            ->relationship('user', 'name')
                            ->default(fn () => Auth::id())
                            ->required(),
                    ]),

                    // Fila 4: Comentarios
                    Textarea::make('descripcion')
                        ->label('Notas / Agenda')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            // -----------------------------------------------------------------
            // 2. EDITAR TAREA / INTERACCIÓN
            // -----------------------------------------------------------------
            EditAction::make('editTarea')
                ->model(Interaccion::class)
                ->modalHeading('Detalle de Interacción')
                ->color('warning') // Botón naranja para distinguir visualmente
                ->form([
                    Grid::make(2)->schema([
                        Select::make('tipo_interaccion') // Ej: Llamada, Whatsapp, Correo
                            ->options([
                                'llamada' => 'Llamada Telefónica',
                                'whatsapp' => 'WhatsApp',
                                'correo' => 'Correo Electrónico',
                                'visita' => 'Visita',
                            ])
                            ->required()
                            ->label('Canal'),

                        DateTimePicker::make('fecha_programada')
                            ->label('Fecha Programada')
                            ->seconds(false)
                            ->required(),
                    ]),

                    Select::make('prospecto_id')
                        ->label('Prospecto')
                        ->relationship('prospecto', 'nombre_completo') // Ajusta el campo nombre
                        ->searchable()
                        ->required(),

                    Textarea::make('observaciones')
                        ->label('Resultado / Notas')
                        ->placeholder('¿Qué sucedió en esta interacción?')
                        ->rows(3)
                        ->columnSpanFull(),

                    // Checkbox rápido para completar la tarea ahí mismo
                    Toggle::make('completada')
                        ->label('Marcar como Completada')
                        ->inline(false),
                ]),
        ];
    }

    /**
     * Se ejecuta al hacer clic en un evento.
     * Firma exacta: 3 argumentos (según la versión Beta).
     */
    public function onEventClick(array $event): void
    {
        // 1. Obtener el ID compuesto (Ej: "agenda_15")
        $fullId = $event['id'] ?? '';
        $parts  = explode('_', $fullId);

        if (count($parts) < 2) return;

        $tipo   = $parts[0]; // 'agenda' o 'tarea'
        $idReal = $parts[1]; // '15'

        // 2. Lanzar el Modal correspondiente
        if ($tipo === 'agenda') {
            $this->mountAction('editAgenda', [
                'record' => $idReal, // Le pasamos el ID real para que sepa qué editar
            ]);
        } elseif ($tipo === 'tarea') {
            $this->mountAction('editTarea', [
                'record' => $idReal,
            ]);
        }
    }
}
