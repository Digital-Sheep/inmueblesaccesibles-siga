<?php

namespace App\Filament\Resources\Configuracion\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cuenta de Usuario')
                    ->description('Credenciales y asignación organizacional.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->label('Nombre Completo')
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('telefono')
                                    ->tel()
                                    ->maxLength(20),

                                // Selector de Sucursal (Clave para los filtros)
                                Select::make('sucursal_id')
                                    ->relationship('sucursal', 'nombre')
                                    ->required() // Todo usuario debe tener sucursal (incluso si es Matriz)
                                    ->searchable()
                                    ->preload(),

                                // Asignación de Roles (Spatie)
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->label('Roles de Seguridad'),

                                Toggle::make('activo')
                                    ->label('Usuario Activo')
                                    ->default(true)
                                    ->helperText('Si se desactiva, no podrá iniciar sesión.'),
                            ]),

                        // Sección de Contraseña (solo requerida al crear)
                        Section::make('Seguridad')
                            ->schema([
                                TextInput::make('password')
                                    ->password()
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state)) // Solo actualiza si escriben algo
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->label('Contraseña'),
                            ])->compact(),
                    ]),
            ]);
    }
}
