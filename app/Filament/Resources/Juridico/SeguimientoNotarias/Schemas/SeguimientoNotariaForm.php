<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Schemas;

use App\Enums\SedeJuicioEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;

class SeguimientoNotariaForm
{
    // ── Form de edición (tabs libres) ──────────────────────────────────────────

    public static function schema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('📋 Información General')
                        ->schema(self::camposInformacionGeneral()),

                    Tab::make('📄 Cesión y Seguimiento')
                        ->schema(self::camposCesionYSeguimiento()),
                ])
                ->columnSpanFull(),
        ];
    }

    // ── Pasos reutilizables por el Wizard del Create ───────────────────────────

    public static function camposInformacionGeneral(): array
    {
        return [
            Section::make('Identificación')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('id_garantia')
                            ->label('ID Garantía')
                            ->placeholder('GAR-XXXXXXXXX')
                            ->maxLength(100),

                        TextInput::make('numero_credito')
                            ->label('Número de Crédito')
                            ->maxLength(100),

                        Select::make('propiedad_id')
                            ->label('Propiedad en SIGA (opcional)')
                            ->relationship('propiedad', 'numero_credito')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Solo si la propiedad ya existe en SIGA')
                            ->columnSpanFull(),

                        TextInput::make('nombre_cliente')
                            ->label('Nombre del Cliente')
                            ->maxLength(200),

                        TextInput::make('administradora')
                            ->label('Administradora')
                            ->maxLength(200),
                    ]),
                ]),

            Section::make('Datos de la Notaría')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('sede')
                            ->label('Sede')
                            ->options(SedeJuicioEnum::class)
                            ->required(),

                        TextInput::make('notario')
                            ->label('Notario')
                            ->maxLength(200),

                        TextInput::make('numero_escritura')
                            ->label('Número de Escritura')
                            ->maxLength(100),

                        DatePicker::make('fecha_escritura')
                            ->label('Fecha de Escritura')
                            ->native(false)
                            ->nullable(),

                        Toggle::make('activo')
                            ->label('Seguimiento Activo')
                            ->inline(false)
                            ->default(true),
                    ]),
                ]),
        ];
    }

    public static function camposDatosNotaria(): array
    {
        return [
            Section::make('Notario y Escritura')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('notario')
                            ->label('Notario')
                            ->maxLength(200),

                        TextInput::make('numero_escritura')
                            ->label('Número de Escritura')
                            ->maxLength(100),

                        DatePicker::make('fecha_escritura')
                            ->label('Fecha de Escritura')
                            ->native(false)
                            ->nullable(),
                    ]),
                ]),
        ];
    }

    public static function camposCesionYSeguimiento(): array
    {
        return [
            Section::make('Cesión de Derechos')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('hay_cesion_derechos')
                            ->label('¿Hay Cesión de Derechos a DIIPA?')
                            ->inline(false)
                            ->live()
                            ->columnSpanFull(),

                        Textarea::make('cedente')
                            ->label('Cedente')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('hay_cesion_derechos')),

                        Textarea::make('cesionario')
                            ->label('Cesionario')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('hay_cesion_derechos')),
                    ]),
                ]),

            Section::make('Estado Actual')
                ->schema([
                    Textarea::make('etapa_actual')
                        ->label('Etapa en que se Encuentra')
                        ->rows(3),

                    Textarea::make('notas_director')
                        ->label('Notas del Director / UCP')
                        ->rows(2),
                ]),
        ];
    }
}
