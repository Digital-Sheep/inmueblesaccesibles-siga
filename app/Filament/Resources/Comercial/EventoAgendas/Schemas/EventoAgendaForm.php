<?php

namespace App\Filament\Resources\Comercial\EventoAgendas\Schemas;

use App\Models\Cliente;
use App\Models\Prospecto;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EventoAgendaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Evento')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        TextInput::make('titulo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                // Tipo de evento
                                Select::make('tipo')
                                    ->required()
                                    ->options([
                                        'CITA_VISITA' => 'Cita Visita Propiedad',
                                        'LLAMADA' => 'Llamada de Seguimiento',
                                        'FIRMA_CONTRATO' => 'Firma en Notaría',
                                        'REUNION_INTERNA' => 'Reunión Interna (Sucursal)',
                                    ])
                                    ->native(false)
                                    ->live(),

                                // Selección de Participante (Prospecto o Cliente)
                                Select::make('participante_id')
                                    ->label('Prospecto/Cliente')
                                    ->options(function () {
                                        // Combinamos Prospectos y Clientes para la selección polimórfica
                                        $prospectos = Prospecto::pluck('nombre_completo', 'id')->toArray();
                                        $clientes = Cliente::pluck('nombre_completo_virtual', 'id')->toArray();

                                        return [
                                            'Prospectos' => $prospectos,
                                            'Clientes' => $clientes,
                                        ];
                                    })
                                    ->searchable()
                                    ->required(),

                                // Asignación del Dueño de la Agenda
                                Select::make('usuario_id')
                                    ->relationship('usuario', 'name')
                                    ->label('Dueño de la Agenda')
                                    ->default(fn() => Auth::id())
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('fecha_inicio')
                                    ->required(),
                                DateTimePicker::make('fecha_fin')
                                    ->required()
                                    ->minDate(fn(Get $get) => $get('fecha_inicio'))
                            ]),

                        Textarea::make('descripcion')
                            ->label('Notas / Agenda del día')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
