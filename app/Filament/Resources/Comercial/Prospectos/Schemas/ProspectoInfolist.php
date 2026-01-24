<?php

namespace App\Filament\Resources\Comercial\Prospectos\Schemas;

use App\Filament\Resources\Comercial\ProcesoVentas\ProcesoVentaResource;
use App\Models\ProcesoVenta;
use App\Models\Propiedad;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Auth;

class ProspectoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Actions::make([
                    Action::make('iniciar_venta')
                        ->label('Iniciar nuevo proceso de venta')
                        ->icon('heroicon-m-currency-dollar')
                        ->color('success')
                        ->button()

                        ->schema([
                            Select::make('propiedad_id')
                                ->label('Propiedad')
                                ->options(
                                    Propiedad::where('estatus_comercial', 'DISPONIBLE')
                                        ->pluck('direccion_completa', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('Solo aparecen propiedades con estatus comercial DISPONIBLE.'),
                        ])
                        ->action(function ($record, array $data) {
                            // Crear Venta
                            $venta = ProcesoVenta::create([
                                'interesado_type' => get_class($record),
                                'interesado_id' => $record->id,

                                'propiedad_id' => $data['propiedad_id'],

                                'vendedor_id' => Auth::id(),

                                'estatus' => 'ACTIVO',
                            ]);

                            // Actualizar Prospecto
                            $record->update(['estatus' => 'APARTADO']);

                            Propiedad::find($data['propiedad_id'])->update([
                                'estatus_comercial' => 'EN_PROCESO',
                            ]);

                            Notification::make()->title('proceso iniciado correctamente')->success()->send();

                            return redirect()->to(
                                ProcesoVentaResource::getUrl('view', ['record' => $venta->id])
                            );
                        })
                        ->visible(function ($record) {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();
                            return $record->estatus !== 'CLIENTE' && $user->can('ventas_crear');
                        }),
                ])
                    ->alignRight(),
                Grid::make(12)
                    ->schema([
                        Group::make()
                            ->columnSpan([
                                'default' => 12,
                                'md' => 5,
                            ])
                            ->schema([
                                // ------------------------------------------------
                                // COLUMNA 1: FICHA TÉCNICA
                                // ------------------------------------------------
                                Section::make('Ficha del Prospecto')
                                    ->icon('heroicon-m-user')
                                    ->schema([
                                        // Estatus destacado
                                        TextEntry::make('estatus')
                                            ->badge()
                                            ->size(TextSize::Large)
                                            ->color(fn(string $state): string => match ($state) {
                                                'NUEVO' => 'info',
                                                'CONTACTADO' => 'warning',
                                                'CITA' => 'primary',
                                                'APARTADO' => 'success',
                                                'CLIENTE' => 'success',
                                                'DESCARTADO' => 'danger',
                                                default => 'gray',
                                            })
                                            ->columnSpanFull(),

                                        TextEntry::make('nombre_completo')
                                            ->label('Cliente')
                                            ->weight(FontWeight::Bold)
                                            ->size(TextSize::Large),

                                        Group::make([
                                            TextEntry::make('celular')
                                                ->label('WhatsApp / Celular')
                                                ->icon('heroicon-m-phone')
                                                ->url(fn($state) => "tel:{$state}")
                                                ->color('primary')
                                                ->weight(FontWeight::Bold),

                                            TextEntry::make('email')
                                                ->icon('heroicon-m-envelope')
                                                ->placeholder('Sin correo registrado')
                                                ->copyable(),
                                        ])->columns(1),

                                        TextEntry::make('sucursal.nombre')
                                            ->label('Sucursal')
                                            ->icon('heroicon-m-building-storefront'),

                                        TextEntry::make('responsable.name')
                                            ->label('Asesor Asignado')
                                            ->icon('heroicon-m-user-circle'),

                                        TextEntry::make('origen')
                                            ->label('Origen del Lead')
                                            ->badge()
                                            ->color('gray'),

                                        // Solo visible si fue descartado
                                        TextEntry::make('motivo_descarte')
                                            ->label('Motivo de Descarte')
                                            ->color('danger')
                                            ->icon('heroicon-m-x-circle')
                                            ->visible(fn($record) => $record->estatus === 'DESCARTADO'),
                                    ]),

                                Section::make('Procesos de Venta')
                                    ->icon('heroicon-m-currency-dollar')
                                    ->compact()
                                    ->schema([
                                        RepeatableEntry::make('procesosVenta')
                                            ->label('')
                                            ->contained(false)
                                            ->schema([
                                                Group::make([
                                                    TextEntry::make('propiedad.direccion_completa')
                                                        ->label('Propiedad')
                                                        ->icon('heroicon-m-arrow-top-right-on-square')
                                                        ->iconPosition(IconPosition::After)
                                                        ->weight(FontWeight::Bold)
                                                        ->color('primary')

                                                        // AQUÍ ESTÁ LA MAGIA:
                                                        ->url(fn($record) => ProcesoVentaResource::getUrl('view', ['record' => $record->id]))
                                                        ->openUrlInNewTab(),

                                                    TextEntry::make('estatus')
                                                        ->badge()
                                                        ->color(fn($state) => match ($state) {
                                                            'ACTIVO' => 'success',
                                                            'CANCELADO' => 'danger',
                                                            default => 'warning'
                                                        }),

                                                    TextEntry::make('created_at')
                                                        ->date('d/M/Y')
                                                        ->size(TextSize::ExtraSmall)
                                                        ->color('gray'),
                                                ])
                                            ])
                                            ->placeholder('Sin procesos de venta iniciados'),
                                    ]),
                            ]),



                        Group::make()
                            ->columnSpan(['default' => 12, 'md' => 7])
                            ->schema([

                                // ------------------------------------------------
                                // 1. SECCIÓN DE AGENDA (PENDIENTES)
                                // ------------------------------------------------
                                Section::make('Próximas Acciones')
                                    ->icon('heroicon-m-bell-alert')
                                    ->headerActions([
                                        Action::make('ver_agenda_completa')
                                            ->label('Ver agenda completa')
                                            ->icon('heroicon-m-calendar-days')
                                            ->url(fn($record) => route('filament.resources.comercial.interacciones.index', [
                                                'tableFilters' => [
                                                    'prospecto' => ['value' => $record->id],
                                                ],
                                            ]))
                                            ->openUrlInNewTab()
                                    ])
                                    ->schema([
                                        RepeatableEntry::make('interaccionesPendientes') // <--- USA LA NUEVA RELACIÓN
                                            ->label('Tareas Pendientes')
                                            ->contained(false)
                                            ->placeholder('No hay tareas pendientes. ¡Agenda un seguimiento!')
                                            ->schema([
                                                // Tarjeta estilo "Alerta"
                                                Group::make()
                                                    ->extraAttributes([
                                                        'style' => 'background-color: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px; margin-bottom: 8px;',
                                                    ])
                                                    ->schema([
                                                        Grid::make(12)->schema([
                                                            // Icono y Tipo
                                                            Group::make([
                                                                TextEntry::make('tipo')
                                                                    ->badge()
                                                                    ->color('warning'),
                                                                TextEntry::make('estatus')
                                                                    ->badge()
                                                                    ->color('danger')
                                                                    ->label('PENDIENTE'),
                                                            ])->columnSpan(4),

                                                            // Fecha Programada (Destacada)
                                                            TextEntry::make('fecha_programada')
                                                                ->label('Programado para:')
                                                                ->dateTime('l d M, h:i A') // Ej: Lunes 20 Ene, 10:00 AM
                                                                ->weight(FontWeight::Bold)
                                                                ->alignRight()
                                                                ->color('danger')
                                                                ->columnSpan(8),

                                                            // Título de la tarea
                                                            TextEntry::make('titulo')
                                                                ->weight(FontWeight::Bold)
                                                                ->size(TextSize::Large)
                                                                ->columnSpan(12),

                                                            TextEntry::make('comentario')
                                                                ->markdown()
                                                                ->columnSpan(12),
                                                        ])
                                                    ])
                                            ])
                                    ])
                                    ->visible(fn($record) => $record->interaccionesPendientes()->count() > 0),

                                // ------------------------------------------------
                                // 2. SECCIÓN DE HISTORIAL (COMPLETADOS)
                                // ------------------------------------------------
                                Section::make('Historial de Actividad')
                                    ->icon('heroicon-m-clock')
                                    ->collapsible()
                                    ->schema([
                                        Group::make()
                                            ->extraAttributes([
                                                'style' => 'max-height: 500px; overflow-y: auto; padding-right: 10px;',
                                            ])
                                            ->schema([
                                                RepeatableEntry::make('interaccionesHistorial')
                                                    ->label('')
                                                    ->contained(false)
                                                    ->schema([
                                                        // Tarjeta estilo "Historial" (Gris/Blanco)
                                                        Group::make()
                                                            ->extraAttributes([
                                                                'style' => 'background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);',
                                                            ])
                                                            ->schema([
                                                                Grid::make(12)->schema([
                                                                    Group::make([
                                                                        TextEntry::make('tipo')
                                                                            ->badge()
                                                                            ->color(fn($state) => match ($state) {
                                                                                'LLAMADA' => 'info',
                                                                                'WHATSAPP' => 'success',
                                                                                'CITA_AGENDADA' => 'primary',
                                                                                default => 'gray'
                                                                            }),
                                                                    ])->columnSpan(4),

                                                                    TextEntry::make('fecha_realizada')
                                                                        ->dateTime('d M Y, h:i A')
                                                                        ->alignRight()
                                                                        ->color('gray')
                                                                        ->size(TextSize::Small)
                                                                        ->columnSpan(8),

                                                                    // Separador
                                                                    Group::make()->columnSpan(12)->extraAttributes(['style' => 'border-bottom: 1px dashed #f3f4f6; margin: 8px 0;']),

                                                                    Group::make([
                                                                        TextEntry::make('titulo')->weight(FontWeight::Bold)->hidden(fn($state) => empty($state)),
                                                                        TextEntry::make('comentario')->prose()->markdown(),
                                                                        TextEntry::make('resultado')->badge()->color('gray')->prefix('Resultado: ')->hidden(fn($state) => empty($state)),
                                                                    ])->columnSpan(12),

                                                                    ImageEntry::make('evidencia')->hidden(fn($state) => empty($state))->columnSpan(12)->imageHeight(80),
                                                                ])
                                                            ])
                                                    ])
                                            ]),
                                    ])
                                    ->compact()
                                    ->visible(
                                        function (): bool {
                                            /** @var \App\Models\User  $user */
                                            $user = Auth::user();

                                            return $user->can('interacciones_ver');
                                        }
                                    ),
                            ]),
                    ])
            ]);
    }
}
