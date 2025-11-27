<?php

namespace App\Filament\Resources\Comercial\Prospectos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

use Illuminate\Support\Facades\Auth;

class ProspectoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        // SECCIÓN 1: DATOS DE CONTACTO (Lo vital)
                        Section::make('Información de Contacto')
                            ->schema([
                                TextInput::make('nombre_completo')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('celular')
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true) // ¡Anti-duplicados!
                                    ->validationMessages([
                                        'unique' => 'Este número ya está registrado con otro asesor.',
                                    ]),

                                TextInput::make('email')
                                    ->email()
                                    ->unique(ignoreRecord: true),
                            ])->columnSpan(2),

                        // SECCIÓN 2: CLASIFICACIÓN Y GESTIÓN
                        Section::make('Seguimiento')
                            ->schema([
                                Select::make('estatus')
                                    ->options([
                                        'NUEVO' => 'Nuevo (Sin tocar)',
                                        'CONTACTADO' => 'Ya hubo contacto',
                                        'CITA' => 'Cita Agendada',
                                        'APARTADO' => 'En Proceso de Apartado',
                                        'CLIENTE' => 'Ya es Cliente (Compró)',
                                        'DESCARTADO' => 'Descartado / No Interesado',
                                    ])
                                    ->default('NUEVO')
                                    ->live() // Para mostrar/ocultar el motivo
                                    ->native(false),

                                // Solo aparece si se descarta
                                Textarea::make('motivo_descarte')
                                    ->label('¿Por qué se descartó?')
                                    ->placeholder('Ej. Solo tiene crédito Infonavit...')
                                    ->visible(fn(Get $get) => $get('estatus') === 'DESCARTADO')
                                    ->required(fn(Get $get) => $get('estatus') === 'DESCARTADO')
                                    ->columnSpanFull(),

                                Select::make('origen')
                                    ->options([
                                        'FACEBOOK' => 'Campaña Facebook',
                                        'WEB' => 'Sitio Web',
                                        'REFERIDO' => 'Referido',
                                        'WALK_IN' => 'Visitó Sucursal',
                                    ]),

                                Select::make('sucursal_id')
                                    ->relationship('sucursal', 'nombre')
                                    ->required(),

                                Select::make('usuario_responsable_id')
                                    ->relationship('responsable', 'name')
                                    ->label('Asesor Asignado')
                                    ->default(fn() => Auth::id()) // Se auto-asigna al crear
                                    ->required(),
                            ])->columnSpan(1),
                    ]),
            ]);
    }
}
