<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios\Schemas;

use App\Enums\NivelPrioridadJuicioEnum;
use App\Enums\SedeJuicioEnum;
use App\Enums\TipoProcesoJuicioEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SeguimientoJuicioInfolist
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
                                        TextEntry::make('id_garantia')
                                            ->label('ID Garantía')
                                            ->default('—'),

                                        TextEntry::make('numero_credito')
                                            ->label('Número de Crédito')
                                            ->default('—'),

                                        TextEntry::make('nombre_cliente')
                                            ->label('Cliente')
                                            ->default('Sin cliente'),

                                        TextEntry::make('sede')
                                            ->label('Sede')
                                            ->badge()
                                            ->formatStateUsing(fn($state) => $state instanceof SedeJuicioEnum ? $state->getLabel() : $state),

                                        TextEntry::make('nivel_prioridad')
                                            ->label('Prioridad')
                                            ->badge()
                                            ->color(fn($state) => $state instanceof NivelPrioridadJuicioEnum ? $state->getColor() : 'gray'),

                                        TextEntry::make('tipo_proceso')
                                            ->label('Tipo de Proceso')
                                            ->badge()
                                            ->color(fn($state) => $state instanceof TipoProcesoJuicioEnum ? $state->getColor() : 'gray')
                                            ->default('—'),

                                        TextEntry::make('abogados.name')
                                            ->label('Abogado(s)')
                                            ->badge()
                                            ->color('info')
                                            ->default('Sin abogado asignado')
                                            ->columnSpanFull(),

                                        TextEntry::make('id')
                                            ->label('Administradora')
                                            ->formatStateUsing(fn($state, $record) => $record->nombre_administradora ?? '—')
                                            ->default('—'),

                                        IconEntry::make('con_demanda')
                                            ->label('Con Demanda')
                                            ->boolean(),

                                        IconEntry::make('hay_cesion_derechos')
                                            ->label('Cesión de Derechos')
                                            ->boolean(),

                                        IconEntry::make('activo')
                                            ->label('Activo')
                                            ->boolean(),

                                        TextEntry::make('ultima_actuacion_at')
                                            ->label('Última Actuación')
                                            ->formatStateUsing(
                                                fn($state, $record) => $record->ultima_actuacion_at
                                                    ? $record->ultima_actuacion_at->format('d/m/Y')
                                                    : 'Sin actuaciones'
                                            )
                                            ->color(fn($record) => $record->esta_rezagado ? 'danger' : 'success'),
                                    ]),
                                ]),
                        ]),

                    Tab::make('⚖️ Juicio')
                        ->schema([
                            Section::make('Partes')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextEntry::make('actor')
                                            ->label('Actor (Demandante)')
                                            ->default('—'),

                                        TextEntry::make('demandado')
                                            ->label('Demandado')
                                            ->default('—'),
                                    ]),
                                ]),

                            Section::make('Expediente')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextEntry::make('numero_expediente')
                                            ->label('Número de Expediente')
                                            ->default('—'),

                                        TextEntry::make('distrito_judicial')
                                            ->label('Distrito Judicial')
                                            ->default('—'),

                                        TextEntry::make('tipo_juicio_materia')
                                            ->label('Tipo de Juicio / Materia')
                                            ->default('—'),

                                        TextEntry::make('via_procesal')
                                            ->label('Vía Procesal')
                                            ->default('—'),

                                        TextEntry::make('juzgado')
                                            ->label('Juzgado')
                                            ->columnSpanFull()
                                            ->default('—'),

                                        TextEntry::make('domicilio')
                                            ->label('Domicilio')
                                            ->columnSpanFull()
                                            ->default('—'),
                                    ]),
                                ]),

                            Section::make('Cesión de Derechos')
                                ->schema([
                                    Grid::make(1)->schema([
                                        TextEntry::make('cedente')
                                            ->label('Cedente')
                                            ->default('—')
                                            ->visible(fn($record) => $record->hay_cesion_derechos),

                                        TextEntry::make('cesionario')
                                            ->label('Cesionario')
                                            ->default('—')
                                            ->visible(fn($record) => $record->hay_cesion_derechos),
                                    ]),
                                ])
                                ->visible(fn($record) => $record->hay_cesion_derechos),

                            Section::make('Seguimiento Narrativo')
                                ->schema([
                                    TextEntry::make('etapa_actual')
                                        ->label('Etapa Actual')
                                        ->default('Sin información')
                                        ->columnSpanFull(),

                                    TextEntry::make('id')
                                        ->label('Estrategia Jurídica')
                                        ->formatStateUsing(function ($state, $record) {
                                            if (! $record->estrategia_juridica_archivo) {
                                                return 'Sin archivo';
                                            }
                                            return basename($record->estrategia_juridica_archivo);
                                        })
                                        ->url(
                                            fn($record) => $record->estrategia_juridica_archivo
                                                ? Storage::disk('private')->temporaryUrl(
                                                    $record->estrategia_juridica_archivo,
                                                    now()->addMinutes(30)
                                                )
                                                : null
                                        )
                                        ->openUrlInNewTab()
                                        ->color('primary')
                                        ->columnSpanFull(),

                                    TextEntry::make('notas_director')
                                        ->label('Notas Director / UCP')
                                        ->default('—')
                                        ->columnSpanFull()
                                        ->visible(function () {
                                            /** @var \App\Models\User $user */
                                            $user = Auth::user();

                                            return $user->can('seguimientojuicios_ver_todos');
                                        }),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
