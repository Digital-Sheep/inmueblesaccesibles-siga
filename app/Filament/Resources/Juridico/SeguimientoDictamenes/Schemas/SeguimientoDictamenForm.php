<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\Schemas;

use App\Enums\ResultadoDictamenEnum;
use App\Enums\TipoProcesoDictamenEnum;
use App\Models\CatAdministradora;
use App\Models\Propiedad;
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
use Filament\Schemas\Components\Utilities\Set;

class SeguimientoDictamenForm
{
    // ── Form de edición (tabs libres) ──────────────────────────────────────────

    public static function schema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('📋 Información General')
                        ->schema(self::camposInformacionGeneral()),

                    Tab::make('⚖️ Dictamen')
                        ->schema(self::camposDictamen()),

                    Tab::make('💰 Valores')
                        ->schema(self::camposValores()),

                    Tab::make('📍 Seguimiento')
                        ->schema(self::camposSeguimiento()),
                ])
                ->columnSpanFull(),
        ];
    }

    // ── Pasos reutilizables por el Wizard del Create ───────────────────────────

    public static function camposInformacionGeneral(array $opcionesTipoProceso = []): array
    {
        $opciones = ! empty($opcionesTipoProceso)
            ? $opcionesTipoProceso
            : TipoProcesoDictamenEnum::class;

        return [
            Section::make('Identificación')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('tipo_proceso')
                            ->label('Tipo de Proceso')
                            ->options($opciones)
                            ->required(),

                        Select::make('solicitante_id')
                            ->label('Solicitado por')
                            ->relationship('solicitante', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        // Propiedad — autocompleta dirección, número de crédito y valores
                        Select::make('propiedad_id')
                            ->label('Propiedad en SIGA (opcional)')
                            ->relationship('propiedad', 'numero_credito')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }

                                $propiedad = Propiedad::with('cotizacionActiva')->find($state);

                                if (! $propiedad) {
                                    return;
                                }

                                // Autocomplete campos de identificación
                                $set('numero_credito', $propiedad->numero_credito);

                                if ($propiedad->direccion_completa) {
                                    $set('direccion', $propiedad->direccion_completa);
                                }

                                // Autocomplete valores desde cotización activa
                                if ($propiedad->cotizacionActiva) {
                                    $set('valor_venta', $propiedad->cotizacionActiva->precio_venta_con_descuento);
                                    $set('valor_sin_remodelacion', $propiedad->cotizacionActiva->precio_sin_remodelacion);
                                }

                                if ($propiedad->precio_lista) {
                                    $set('valor_garantia', $propiedad->precio_lista);
                                }

                                if ($propiedad->precio_valor_comercial) {
                                    $set('valor_comercial_aproximado', $propiedad->precio_valor_comercial);
                                }
                            })
                            ->helperText('Al seleccionar, se autocompletan dirección, crédito y valores')
                            ->columnSpanFull(),

                        TextInput::make('numero_credito')
                            ->label('Número de Crédito')
                            ->maxLength(100),

                        Select::make('administradora_id')
                            ->label('Administradora')
                            ->options(
                                CatAdministradora::where('activo', true)
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->nullable(),

                        TextInput::make('numero_juicio')
                            ->label('Número de Juicio')
                            ->maxLength(200),

                        TextInput::make('numero_expediente')
                            ->label('Número de Expediente')
                            ->maxLength(200),

                        TextInput::make('jurisdiccion')
                            ->label('Jurisdicción')
                            ->maxLength(200),

                        TextInput::make('via_procesal')
                            ->label('Vía Procesal')
                            ->maxLength(100),

                        Textarea::make('direccion')
                            ->label('Dirección de la Garantía')
                            ->rows(2)
                            ->columnSpanFull(),

                        // Cliente opcional
                        Select::make('cliente_id')
                            ->label('Ligar a Cliente (opcional)')
                            ->relationship('cliente', 'nombres')
                            ->getOptionLabelFromRecordUsing(fn($record) => trim("{$record->nombres} {$record->apellido_paterno} {$record->apellido_materno}"))
                            ->searchable(['nombres', 'apellido_paterno', 'apellido_materno'])
                            ->preload()
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                ]),
        ];
    }

    public static function camposDictamen(): array
    {
        return [
            Section::make('Dictamen Jurídico')
                ->schema([
                    Grid::make(2)->schema([
                        FileUpload::make('dictamen_juridico_archivo')
                            ->label('Archivo del Dictamen Jurídico')
                            ->disk('private')
                            ->directory(
                                fn(Get $get) =>
                                'juridico/dictamenes/' . ($get('id') ?? 'nuevo') . '/juridico'
                            )
                            ->acceptedFileTypes(['application/pdf', 'application/x-pdf', 'application/octet-stream', 'image/jpeg', 'image/png'])
                            ->rules(['mimes:pdf,jpg,jpeg,png'])
                            ->maxSize(102400)
                            ->preserveFilenames(false)
                            ->helperText('PDF o imagen. Máx. 100MB.')
                            ->columnSpanFull(),

                        Select::make('dictamen_juridico_resultado')
                            ->label('Resultado Jurídico')
                            ->options(ResultadoDictamenEnum::class)
                            ->nullable(),

                        TextInput::make('disponibilidad')
                            ->label('Disponibilidad')
                            ->maxLength(200),
                    ]),
                ]),

            Section::make('Carta de Intención')
                ->schema([
                    FileUpload::make('carta_intencion_archivo')
                        ->label('Carta de Intención')
                        ->disk('private')
                        ->directory(
                            fn(Get $get) =>
                            'juridico/dictamenes/' . ($get('id') ?? 'nuevo') . '/carta-intencion'
                        )
                        ->acceptedFileTypes(['application/pdf', 'application/x-pdf', 'application/octet-stream', 'image/jpeg', 'image/png'])
                        ->rules(['mimes:pdf,jpg,jpeg,png'])
                        ->maxSize(102400)
                        ->preserveFilenames(false)
                        ->helperText('Máx. 100MB.')
                        ->nullable(),
                ]),

            Section::make('Cofinavit')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('tiene_cofinavit')
                            ->label('¿Tiene Cofinavit?')
                            ->inline(false)
                            ->live(),

                        TextInput::make('valor_cofinavit')
                            ->label('Valor del Cofinavit')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn(Get $get) => $get('tiene_cofinavit')),
                    ]),
                ]),

            Section::make('Dictamen Registral')
                ->schema([
                    Grid::make(2)->schema([
                        FileUpload::make('dictamen_registral_archivo')
                            ->label('Archivo del Dictamen Registral')
                            ->disk('private')
                            ->directory(
                                fn(Get $get) =>
                                'juridico/dictamenes/' . ($get('id') ?? 'nuevo') . '/registral'
                            )
                            ->acceptedFileTypes(['application/pdf', 'application/x-pdf', 'application/octet-stream', 'image/jpeg', 'image/png'])
                            ->rules(['mimes:pdf,jpg,jpeg,png'])
                            ->maxSize(102400)
                            ->preserveFilenames(false)
                            ->helperText('PDF o imagen. Máx. 100MB.')
                            ->columnSpanFull(),

                        Select::make('dictamen_registral_resultado')
                            ->label('Resultado Registral')
                            ->options(ResultadoDictamenEnum::class)
                            ->nullable(),
                    ]),
                ]),
        ];
    }

    public static function camposValores(): array
    {
        return [
            Section::make('Valores de Referencia')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('valor_garantia')
                            ->label('Valor de la Garantía')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('valor_catastral')
                            ->label('Valor Catastral')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Campo manual — el sistema no lo obtiene automáticamente'),

                        TextInput::make('valor_comercial_aproximado')
                            ->label('Valor Comercial Aproximado')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('valor_venta')
                            ->label('Valor de Venta')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Auto desde cotización activa si hay propiedad vinculada'),

                        TextInput::make('valor_sin_remodelacion')
                            ->label('Valor Sin Remodelación')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Auto desde cotización activa si hay propiedad vinculada'),
                    ]),
                ]),
        ];
    }

    public static function camposSeguimiento(): array
    {
        return [
            Section::make('Estado')
                ->schema([
                    Textarea::make('etapa_actual')
                        ->label('Etapa Actual')
                        ->rows(3)
                        ->helperText('Se actualiza automáticamente al registrar actuaciones'),

                    Textarea::make('notas')
                        ->label('Notas Internas')
                        ->rows(2),

                    Toggle::make('activo')
                        ->label('Dictamen Activo')
                        ->inline(false)
                        ->default(true),
                ]),
        ];
    }
}
