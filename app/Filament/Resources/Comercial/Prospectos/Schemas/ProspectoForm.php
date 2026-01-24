<?php

namespace App\Filament\Resources\Comercial\Prospectos\Schemas;

use App\Models\User;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProspectoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información del Prospecto')
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
                                    ->required()
                                    ->maxLength(20),

                                TextInput::make('email')
                                    ->label('Correo electrónico')
                                    ->email()
                                    ->maxLength(255),

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
                                    ->native(false)
                                    ->required(),

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
                                    ->label('Sucursal')
                                    ->default(fn() => Auth::user()->sucursal_id)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('usuario_responsable_id', null);
                                    })
                                    ->disabled(function () {
                                        /** @var \App\Models\User $user */
                                        $user = Auth::user();

                                        return ! ($user->can('prospectos_ver_todos') && $user->can('prospectos_reasignar'));
                                    })
                                    ->dehydrated()
                                    ->native(false),

                                Select::make('usuario_responsable_id')
                                    ->label('Asesor asignado')
                                    ->options(function (Get $get) {
                                        $sucursalId = $get('sucursal_id');

                                        if (! $sucursalId) {
                                            return [];
                                        }

                                        return User::where('sucursal_id', $sucursalId)
                                            ->pluck('name', 'id');
                                    })
                                    ->default(fn() => Auth::id())
                                    ->required()
                                    ->disabled(function () {
                                        /** @var \App\Models\User $user */
                                        $user = Auth::user();

                                        return !$user->can('prospectos_reasignar');
                                    })
                                    ->dehydrated()
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
            ]);
    }
}
