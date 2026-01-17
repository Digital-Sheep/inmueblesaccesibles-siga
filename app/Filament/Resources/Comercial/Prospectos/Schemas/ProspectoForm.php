<?php

namespace App\Filament\Resources\Comercial\Prospectos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

use Illuminate\Support\Facades\Auth;

class ProspectoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Toggle::make('modo_edicion')
                    ->label('Habilitar edición')
                    ->onColor('success')
                    ->offColor('gray')
                    ->default(false)
                    ->live()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->visibleOn('edit')
                    ->hidden(fn(Get $get) => $get('estatus') === 'CLIENTE'),

                Group::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nombre_completo')
                                    ->label('Nombre completo')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('celular')
                                    ->label('WhatsApp / Celular')
                                    ->tel()
                                    ->required(),

                                TextInput::make('email')
                                    ->label('Correo electrónico')
                                    ->email(),

                                Select::make('estatus')
                                    ->options([
                                        'NUEVO' => 'Nuevo',
                                        'CONTACTADO' => 'Ya hubo contacto',
                                        'CITA' => 'Cita agendada',
                                        'APARTADO' => 'En proceso de apartado',
                                        'CLIENTE' => 'Ya es cliente (compró)',
                                        'DESCARTADO' => 'Descartado / No interesado',
                                    ])
                                    ->default('NUEVO')
                                    ->live()
                                    ->native(false),

                                Select::make('origen')
                                    ->options([
                                        'CALL' => 'Llamó a la sucursal',
                                        'FACEBOOK' => 'Campaña Facebook',
                                        'WEB' => 'Sitio web',
                                        'REFERIDO' => 'Referido',
                                        'WALK_IN' => 'Visitó sucursal',
                                        'OTRO' => 'Otro',
                                    ])
                                    ->native(false),

                                Select::make('sucursal_id')
                                    ->relationship('sucursal', 'nombre')
                                    ->default(fn() => Auth::user()->sucursal_id)
                                    ->required()
                                    ->native(false),

                                Select::make('usuario_responsable_id')
                                    ->relationship('responsable', 'name')
                                    ->label('Asesor asignado')
                                    ->default(fn() => Auth::id())
                                    ->required()
                                    ->native(false),

                                Textarea::make('motivo_descarte')
                                    ->label('¿Por qué se descartó?')
                                    ->rows(2)
                                    ->placeholder('Ej. Solo tiene crédito Infonavit...')
                                    ->visible(fn(Get $get) => $get('estatus') === 'DESCARTADO')
                                    ->required(fn(Get $get) => $get('estatus') === 'DESCARTADO')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->disabled(function (string $operation, Get $get) {
                        if ($operation === 'create') {
                            return false;
                        }

                        return ! $get('modo_edicion');
                    }),

                Section::make('Historial de Interacciones')
                    ->collapsed()
                    ->description('Agrega notas rápidas o llamadas')
                    ->schema([
                        Repeater::make('interacciones')
                            ->relationship()
                            ->label('Interacciones')
                            ->addActionLabel('Agregar Nueva Nota/Llamada')
                            ->reorderable(false)
                            ->collapsible()
                            ->cloneable(false)
                            ->grid(1)
                            ->itemLabel(fn(array $state): ?string => $state['titulo'] ?? null)
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('tipo_interaccion')
                                        ->options(['llamada' => 'Llamada', 'whatsapp' => 'WhatsApp', 'nota' => 'Nota'])
                                        ->required(),
                                    DateTimePicker::make('fecha_programada')
                                        ->required(),
                                ]),
                                TextInput::make('titulo')->required()->columnSpanFull(),
                                Textarea::make('observaciones')->rows(2)->columnSpanFull(),
                            ]),
                    ])
                    ->hidden(function (string $operation, Get $get) {
                        if ($operation === 'create') {
                            return true;
                        }
                    }),
            ]);
    }
}
