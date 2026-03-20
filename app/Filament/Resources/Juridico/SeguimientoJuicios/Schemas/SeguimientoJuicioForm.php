<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Schemas;

use App\Enums\NivelPrioridadJuicioEnum;
use App\Enums\SedeJuicioEnum;
use App\Enums\TipoProcesoJuicioEnum;
use App\Models\CatAdministradora;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;

class SeguimientoJuicioForm
{
    // ── Form de edición (tabs libres) ──────────────────────────────────────────

    public static function schema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('📋 Información General')
                        ->schema(self::camposInformacionGeneral()),

                    Tab::make('⚖️ Datos del Juicio')
                        ->schema(self::camposDatosJuicio()),

                    Tab::make('📍 Seguimiento')
                        ->schema(self::camposSeguimiento()),
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
                            ->live()
                            ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
                                if (! $state) {
                                    return;
                                }

                                $propiedad = \App\Models\Propiedad::find($state);

                                if (! $propiedad) {
                                    return;
                                }

                                // Llenar domicilio con direccion_completa de la propiedad
                                if ($propiedad->direccion_completa) {
                                    $set('domicilio', $propiedad->direccion_completa);
                                }

                                // También llenar número de crédito si está vacío
                                $set('numero_credito', $propiedad->numero_credito);
                            })
                            ->helperText('Al seleccionar, se autocompleta el domicilio y número de crédito')
                            ->columnSpanFull(),

                        TextInput::make('nombre_cliente')
                            ->label('Nombre del Cliente')
                            ->placeholder('Sin cliente')
                            ->maxLength(200),

                        Select::make('administradora_id')
                            ->label('Administradora')
                            ->options(
                                CatAdministradora::where('activo', true)
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->nullable()
                            ->helperText('Selecciona del catálogo de administradoras'),
                    ]),
                ]),

            Section::make('Clasificación')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('sede')
                            ->label('Sede')
                            ->options(SedeJuicioEnum::class)
                            ->required(),

                        Select::make('nivel_prioridad')
                            ->label('Nivel de Prioridad')
                            ->options(NivelPrioridadJuicioEnum::class)
                            ->required()
                            ->default(NivelPrioridadJuicioEnum::SIN_REVISAR),

                        Select::make('tipo_proceso')
                            ->label('Tipo de Proceso')
                            ->options(TipoProcesoJuicioEnum::class)
                            ->nullable(),

                        Select::make('abogados')
                            ->label('Abogados a Cargo')
                            ->relationship(
                                name: 'abogados',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->role('abogado')->where('activo', true)
                            )
                            ->multiple()
                            ->maxItems(3)
                            ->searchable()
                            ->preload()
                            ->helperText('Máximo 3 abogados por juicio')
                            ->columnSpanFull(),

                        Toggle::make('con_demanda')
                            ->label('¿Tiene Demanda Presentada?')
                            ->inline(false)
                            ->default(true)
                            ->helperText('Activa cuando ya existe demanda formal presentada ante el juzgado'),

                        Toggle::make('activo')
                            ->label('Juicio Activo')
                            ->inline(false)
                            ->default(true),
                    ]),
                ]),
        ];
    }

    public static function camposDatosJuicio(): array
    {
        return [
            Section::make('Partes')
                ->schema([
                    Textarea::make('actor')
                        ->label('Actor (Demandante)')
                        ->rows(2)
                        ->maxLength(300),

                    Textarea::make('demandado')
                        ->label('Demandado')
                        ->rows(2)
                        ->maxLength(300),
                ]),

            Section::make('Expediente')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('numero_expediente')
                            ->label('Número de Expediente')
                            ->maxLength(200),

                        TextInput::make('distrito_judicial')
                            ->label('Distrito Judicial')
                            ->maxLength(200),

                        TextInput::make('tipo_juicio_materia')
                            ->label('Tipo de Juicio / Materia')
                            ->maxLength(200),

                        TextInput::make('via_procesal')
                            ->label('Vía Procesal')
                            ->maxLength(100),

                        Textarea::make('juzgado')
                            ->label('Juzgado')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('domicilio')
                            ->label('Domicilio del Inmueble')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                ]),

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
                            ->visible(fn(Get $get) => $get('hay_cesion_derechos')),

                        Textarea::make('cesionario')
                            ->label('Cesionario')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => $get('hay_cesion_derechos')),
                    ]),
                ]),
        ];
    }

    public static function camposSeguimiento(): array
    {
        return [
            Section::make('Estado Actual')
                ->schema([
                    Textarea::make('etapa_actual')
                        ->label('Etapa en que se Encuentra')
                        ->rows(3),

                    FileUpload::make('estrategia_juridica_archivo')
                        ->label('Estrategia Jurídica (archivo)')
                        ->disk('private')
                        ->directory(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $idGarantia = $get('id_garantia') ?? 'sin-garantia';
                            return 'juridico/juicios/' . $idGarantia . '/estrategia';
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(20480) // 20 MB
                        ->preserveFilenames(false)
                        ->helperText('PDF, imagen o Word. Máx. 20MB.'),

                    Textarea::make('notas_director')
                        ->label('Notas del Director / UCP')
                        ->rows(2),
                ]),
        ];
    }
}
