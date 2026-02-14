<?php

namespace App\Filament\Resources\Configuracion\EtapasProcesales\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;

class EtapaProcesalForm
{
    public static function schema(): array
    {
        return [
            Section::make('Información general')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre de la etapa')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Demanda, Sentencia Firme, Adjudicado')
                        ->columnSpan(1),

                    Forms\Components\Select::make('tipo_juicio_id')
                        ->label('Tipo de juicio')
                        ->relationship('tipoJuicio', 'nombre')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Opcional: Si aplica solo a un tipo específico')
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(2)
                        ->placeholder('Descripción breve de esta etapa procesal...')
                        ->columnSpanFull(),
                ]),

            Section::make('⚖️ Configuración jurídica')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('fase')
                        ->label('Fase del juicio')
                        ->options([
                            'FASE_1' => '📋 Fase 1 - Inicio',
                            'FASE_2' => '⚖️ Fase 2 - En Proceso',
                            'FASE_3' => '✅ Fase 3 - Finalizado',
                        ])
                        // ->required()
                        ->native(false)
                        ->helperText('Fase del proceso jurídico')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('dias_estimados')
                        ->label('Días máximos estimados')
                        ->integer()
                        ->nullable()
                        ->minValue(1)
                        ->suffix('días')
                        ->helperText('Tiempo estimado de duración')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('orden')
                        ->label('Orden de secuencia')
                        ->integer()
                        ->default(1)
                        ->minValue(1)
                        ->helperText('Orden en el flujo del juicio')
                        ->columnSpan(1),
                ]),

            Section::make('💰 Configuración para cotización')
                ->description('Define si esta etapa se usa en el cotizador y su porcentaje de inversión')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Forms\Components\Toggle::make('aplica_para_cotizacion')
                        ->label('¿Usar en cotizador?')
                        ->default(true)
                        ->live()
                        ->helperText('Si está activo, aparecerá en el cotizador')
                        ->columnSpan(1),

                    Forms\Components\Select::make('fase_cotizacion')
                        ->label('Fase para cotización')
                        ->options([
                            'FASE_1' => 'Fase 1 (35% inversión)',
                            'FASE_2' => 'Fase 2 (20% inversión)',
                            'FASE_3' => 'Fase 3 (15% inversión)',
                        ])
                        ->required(fn(Get $get) => $get('aplica_para_cotizacion'))
                        ->visible(fn(Get $get) => $get('aplica_para_cotizacion'))
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $porcentajes = [
                                'FASE_1' => 35,
                                'FASE_2' => 20,
                                'FASE_3' => 15,
                            ];
                            $set('porcentaje_inversion', $porcentajes[$state] ?? 0);
                        })
                        ->helperText('Determina el % de inversión automático')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('porcentaje_inversion')
                        ->label('% de inversión')
                        ->numeric()
                        ->required(fn(Get $get) => $get('aplica_para_cotizacion'))
                        ->visible(fn(Get $get) => $get('aplica_para_cotizacion'))
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->helperText('Porcentaje de utilidad esperada')
                        ->live()
                        ->columnSpan(1),

                    Forms\Components\Placeholder::make('info_cotizacion')
                        ->label('')
                        ->visible(fn(Get $get) => $get('aplica_para_cotizacion'))
                        ->content(fn(Get $get) => new HtmlString(
                            self::getEjemploCotizacion($get('porcentaje_inversion'))
                        ))
                        ->columnSpanFull(),
                ]),

            Section::make('🔧 Estado')
                ->columns(1)
                ->schema([
                    Forms\Components\Toggle::make('activo')
                        ->label('Etapa activa')
                        ->default(true)
                        ->helperText('Solo las etapas activas aparecen en el sistema'),
                ]),
        ];
    }

    /**
     * Generar HTML de ejemplo de cotización
     */
    protected static function getEjemploCotizacion(?float $porcentaje): string
    {
        if (!$porcentaje) {
            $porcentaje = 0;
        }

        $costoEjemplo = 500000;
        $precioCalculado = $porcentaje > 0
            ? $costoEjemplo / (1 - ($porcentaje / 100))
            : 0;

        return sprintf(
            '<div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; border-radius: 4px; margin-top: 8px;">
                <strong style="color: #1e40af;">💡 Ejemplo de cálculo:</strong><br>
                <span style="font-size: 0.875rem; color: #1e3a8a;">
                    Si el costo total es $%s y el %% inversión es %s%%:<br>
                    Precio sugerido = $%s / (1 - 0.%s) = <strong>$%s</strong>
                </span>
            </div>',
            number_format($costoEjemplo, 2),
            number_format($porcentaje, 0),
            number_format($costoEjemplo, 2),
            str_pad((string)$porcentaje, 2, '0', STR_PAD_LEFT),
            number_format($precioCalculado, 2)
        );
    }
}
