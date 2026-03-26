<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes\Schemas;

use App\Enums\EstatusDictamenEnum;
use App\Enums\ResultadoDictamenEnum;
use App\Enums\TipoProcesoDictamenEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Storage;

class SeguimientoDictamenInfolist
{
    public static function schema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('📋 General')
                        ->schema([
                            Section::make('Identificación')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('tipo_proceso')
                                            ->label('Tipo de Proceso')
                                            ->badge()
                                            ->color(fn ($state) => $state instanceof TipoProcesoDictamenEnum
                                                ? $state->getColor() : 'gray'),

                                        TextEntry::make('estatus')
                                            ->label('Estatus')
                                            ->badge()
                                            ->color(fn ($state) => $state instanceof EstatusDictamenEnum
                                                ? $state->getColor() : 'gray'),

                                        TextEntry::make('numero_credito')
                                            ->label('Número de Crédito')
                                            ->default('—'),

                                        TextEntry::make('solicitante.name')
                                            ->label('Solicitado por')
                                            ->default('—'),

                                        TextEntry::make('id')
                                            ->label('Administradora')
                                            ->formatStateUsing(fn ($state, $record) =>
                                                $record->catAdministradora?->nombre ?? '—'
                                            ),

                                        TextEntry::make('numero_expediente')
                                            ->label('Núm. Expediente')
                                            ->default('—'),

                                        TextEntry::make('numero_juicio')
                                            ->label('Núm. Juicio')
                                            ->default('—'),

                                        TextEntry::make('jurisdiccion')
                                            ->label('Jurisdicción')
                                            ->default('—'),

                                        TextEntry::make('via_procesal')
                                            ->label('Vía Procesal')
                                            ->default('—'),

                                        TextEntry::make('direccion')
                                            ->label('Dirección')
                                            ->default('—')
                                            ->columnSpanFull(),

                                        TextEntry::make('cliente.nombre_completo')
                                            ->label('Cliente')
                                            ->default('Sin cliente vinculado'),

                                        TextEntry::make('ultima_actuacion_at')
                                            ->label('Última Actuación')
                                            ->formatStateUsing(fn ($state, $record) => $record->ultima_actuacion_at
                                                ? $record->ultima_actuacion_at->format('d/m/Y')
                                                : '—'
                                            )
                                            ->color(fn ($record) => $record->esta_rezagado ? 'danger' : 'success'),
                                    ]),
                                ]),
                        ]),

                    Tab::make('⚖️ Dictamen')
                        ->schema([
                            Section::make('Dictamen Jurídico')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextEntry::make('dictamen_juridico_resultado')
                                            ->label('Resultado Jurídico')
                                            ->badge()
                                            ->color(fn ($state) => $state instanceof ResultadoDictamenEnum
                                                ? $state->getColor() : 'gray')
                                            ->default('Sin resultado'),

                                        TextEntry::make('disponibilidad')
                                            ->label('Disponibilidad')
                                            ->default('—'),

                                        TextEntry::make('id')
                                            ->label('Archivo Jurídico')
                                            ->formatStateUsing(fn ($state, $record) => $record->dictamen_juridico_archivo
                                                ? basename($record->dictamen_juridico_archivo)
                                                : 'Sin archivo'
                                            )
                                            ->url(fn ($record) => $record->dictamen_juridico_archivo
                                                ? Storage::disk('private')->temporaryUrl(
                                                    $record->dictamen_juridico_archivo,
                                                    now()->addMinutes(30)
                                                ) : null
                                            )
                                            ->color('info')
                                            ->openUrlInNewTab()
                                            ->columnSpanFull(),
                                    ]),
                                ]),

                            Section::make('Carta de Intención')
                                ->schema([
                                    TextEntry::make('id')
                                        ->label('Carta de Intención')
                                        ->formatStateUsing(fn ($state, $record) => $record->carta_intencion_archivo
                                            ? basename($record->carta_intencion_archivo)
                                            : 'Sin archivo'
                                        )
                                        ->url(fn ($record) => $record->carta_intencion_archivo
                                            ? Storage::disk('private')->temporaryUrl(
                                                $record->carta_intencion_archivo,
                                                now()->addMinutes(30)
                                            ) : null
                                        )
                                        ->color('info')
                                        ->openUrlInNewTab(),
                                ]),

                            Section::make('Cofinavit')
                                ->schema([
                                    Grid::make(2)->schema([
                                        IconEntry::make('tiene_cofinavit')
                                            ->label('¿Tiene Cofinavit?')
                                            ->boolean(),

                                        TextEntry::make('valor_cofinavit')
                                            ->label('Valor Cofinavit')
                                            ->money('MXN')
                                            ->default('—')
                                            ->visible(fn ($record) => $record->tiene_cofinavit),
                                    ]),
                                ]),

                            Section::make('Dictamen Registral')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextEntry::make('dictamen_registral_resultado')
                                            ->label('Resultado Registral')
                                            ->badge()
                                            ->color(fn ($state) => $state instanceof ResultadoDictamenEnum
                                                ? $state->getColor() : 'gray')
                                            ->default('Sin resultado'),

                                        TextEntry::make('id')
                                            ->label('Archivo Registral')
                                            ->formatStateUsing(fn ($state, $record) => $record->dictamen_registral_archivo
                                                ? basename($record->dictamen_registral_archivo)
                                                : 'Sin archivo'
                                            )
                                            ->url(fn ($record) => $record->dictamen_registral_archivo
                                                ? Storage::disk('private')->temporaryUrl(
                                                    $record->dictamen_registral_archivo,
                                                    now()->addMinutes(30)
                                                ) : null
                                            )
                                            ->color('info')
                                            ->openUrlInNewTab()
                                            ->columnSpanFull(),
                                    ]),
                                ]),
                        ]),

                    Tab::make('💰 Valores')
                        ->schema([
                            Section::make('Valores de Referencia')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('valor_garantia')
                                            ->label('Valor de la Garantía')
                                            ->money('MXN')
                                            ->default('—'),

                                        TextEntry::make('valor_catastral')
                                            ->label('Valor Catastral')
                                            ->money('MXN')
                                            ->default('—'),

                                        TextEntry::make('valor_comercial_aproximado')
                                            ->label('Valor Comercial Aprox.')
                                            ->money('MXN')
                                            ->default('—'),

                                        TextEntry::make('valor_venta')
                                            ->label('Valor de Venta')
                                            ->money('MXN')
                                            ->default('—'),

                                        TextEntry::make('valor_sin_remodelacion')
                                            ->label('Valor Sin Remodelación')
                                            ->money('MXN')
                                            ->default('—'),
                                    ]),
                                ]),
                        ]),

                    Tab::make('📍 Seguimiento')
                        ->schema([
                            Section::make('Estado Actual')
                                ->schema([
                                    TextEntry::make('etapa_actual')
                                        ->label('Etapa Actual')
                                        ->default('Sin información')
                                        ->columnSpanFull(),

                                    TextEntry::make('notas')
                                        ->label('Notas Internas')
                                        ->default('—')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
