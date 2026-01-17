<?php

namespace App\Filament\Resources\Juridico\Dictamens\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DictamenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumen de Solicitud')
                    ->schema([
                        // Campos de solo lectura para contexto
                        TextInput::make('nombre_proveedor')->disabled(),
                        TextInput::make('numero_credito')->disabled(),
                        TextEntry::make('propiedad.direccion_completa'),
                    ])->columns(3),

                Section::make('Resolución Jurídica')
                    ->schema([
                        Select::make('estatus')
                            ->options([
                                'EN_REVISION' => 'En Revisión (Investigando)',
                                'TERMINADO' => 'Dictamen Concluido',
                            ])
                            ->required()
                            ->reactive(), // Para mostrar campos condicionales

                        // Solo aparecen si ya se va a terminar
                        Select::make('resultado_final')
                            ->label('Veredicto')
                            ->options([
                                'POSITIVO' => '✅ Positivo (Viable)',
                                'NEGATIVO' => '❌ Negativo (No Viable)',
                                'CAMBIO' => '🔄 Requiere Cambio',
                            ])
                            ->visible(fn(Get $get) => $get('estatus') === 'TERMINADO')
                            ->required(fn(Get $get) => $get('estatus') === 'TERMINADO'),

                        Select::make('nomenclatura_generada')
                            ->label('Nomenclatura Asignada')
                            ->options([
                                'R2' => 'R2 - Positivo (Viable para Venta)',
                                'R1' => 'R1 - Negativo (Requiere Cambio)',
                                'RB' => 'RB - Rescisión (Jurídico)',
                                'RV' => 'RV - Cambio Voluntario',
                                'R-INV' => 'R-INV - Inversión Pura',
                            ])
                            ->visible(fn(Get $get) => $get('estatus') === 'TERMINADO')
                            ->required(),

                        RichEditor::make('observaciones_finales')
                            ->label('Análisis Legal')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Hallazgos Registrales (RPPC)')
                    ->schema([
                        TextInput::make('folio_real_rppc')
                            ->label('Folio Real / Electrónico'),

                        Repeater::make('gravamenes_detectados')
                            ->label('Cargas y Gravámenes Adicionales')
                            ->schema([
                                Select::make('tipo')
                                    ->options(['HIPOTECA' => 'Hipoteca', 'EMBARGO' => 'Embargo', 'OTRO' => 'Otro']),
                                TextInput::make('acreedor')->label('Institución/Persona'),
                                TextInput::make('monto')->numeric()->prefix('$'),
                            ])
                            ->columns(3)
                            ->defaultItems(0),
                    ]),
            ]);
    }
}
