<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProcesoVentaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('folio_apartado')->label('Folio')->weight('bold'),
                            TextEntry::make('estatus')
                                ->badge()
                                ->color(fn($state) => match ($state) {
                                    'ACTIVO' => 'success',
                                    'CANCELADO' => 'danger',
                                    default => 'warning'
                                }),
                            TextEntry::make('propiedad.direccion_completa')->label('Propiedad'),
                            TextEntry::make('interesado.nombre_completo')->label('Cliente'),
                        ])
                    ]),
                Tabs::make('Expediente')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('fecha_inicio')->date(),
                                    TextEntry::make('vendedor.name')->label('Asesor Responsable'),
                                    TextEntry::make('observaciones')->columnSpanFull(),
                                ])
                            ]),

                        Tab::make('Documentos')
                            ->icon('heroicon-m-document-text')
                            ->badge('3')
                            ->schema([
                                TextEntry::make('aviso')
                                    ->label('')
                                    ->default('Aquí aparecerá la lista de documentos cargados (INE, Comprobante, etc).')
                                    ->color('gray'),
                            ]),

                        Tab::make('Mesa de Control Jurídico')
                            ->icon('heroicon-m-scale')

                            ->visible(function() {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $user->canAny([
                                    'ver_mesa_control_juridica',
                                    'gestionar_toda_la_red',
                                    'dictaminar_viabilidad'
                                ]);
                            })
                            ->schema([
                                Section::make('Dictaminación')
                                    ->schema([
                                        TextEntry::make('estatus_legal')->label('Fase Legal Actual'),
                                        TextEntry::make('abogado_asignado.name')->label('Abogado a cargo'),
                                        TextEntry::make('fecha_firma_escrituras')->date(),
                                    ])
                            ]),

                        // PESTAÑA D: PAGOS Y FINANZAS
                        Tab::make('Pagos')
                            ->icon('heroicon-m-banknotes')
                            ->visible(function() {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                return $user->can('ver_tablero_finanzas');
                            })
                            ->schema([
                                // ... Tabla de pagos
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }
}
