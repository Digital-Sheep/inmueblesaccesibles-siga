<?php

namespace App\Filament\Resources\Configuracion\Roles\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
// use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;

// use Filament\Tables\Columns\Layout\Split;

class RolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // 📊 HEADER: Información Principal
                Section::make([
                    Grid::make(2)
                        ->schema([
                            // 🏷️ NOMBRE DEL ROL
                            Group::make()
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('Nombre del Rol')
                                        ->size(TextSize::Large)
                                        ->weight(FontWeight::Bold)
                                        ->icon('heroicon-o-shield-check')
                                        ->iconColor('primary')
                                        ->copyable()
                                        ->copyMessage('Rol copiado'),
                                ])
                                ->columnSpan(1),

                            // 📊 ESTADÍSTICAS
                            Group::make()
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('users_count')
                                                ->label('Usuarios Asignados')
                                                ->getStateUsing(fn($record) => $record->users()->count())
                                                ->badge()
                                                ->color(fn($state) => match (true) {
                                                    $state === 0 => 'gray',
                                                    $state <= 5 => 'info',
                                                    $state <= 20 => 'success',
                                                    default => 'warning',
                                                })
                                                ->icon('heroicon-o-user-group'),

                                            TextEntry::make('permissions_count')
                                                ->label('Permisos Asignados')
                                                ->getStateUsing(fn($record) => $record->permissions()->count())
                                                ->badge()
                                                ->color(fn($state) => match (true) {
                                                    $state === 0 => 'danger',
                                                    $state <= 20 => 'warning',
                                                    $state <= 50 => 'info',
                                                    default => 'success',
                                                })
                                                ->icon('heroicon-o-key'),
                                        ]),
                                ])
                                ->columnSpan(1),
                        ]),
                ]),

                // 📅 FECHAS
                Section::make('Información del Sistema')
                    ->description('Fechas de creación y última modificación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Fecha de Creación')
                                    ->dateTime('d/M/Y H:i:s')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Última Modificación')
                                    ->dateTime('d/M/Y H:i:s')
                                    ->since()
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // 👥 USUARIOS CON ESTE ROL
                Section::make('Usuarios con este Rol')
                    ->description('Lista de usuarios que tienen asignado este rol')
                    ->schema([
                        RepeatableEntry::make('users')
                            ->label(false)
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Nombre')
                                            ->icon('heroicon-o-user')
                                            ->weight(FontWeight::Medium),

                                        TextEntry::make('email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable(),

                                        TextEntry::make('sucursal.nombre')
                                            ->label('Sucursal')
                                            ->icon('heroicon-o-building-office-2')
                                            ->badge()
                                            ->color('info')
                                            ->default('Sin asignar'),
                                    ]),
                            ])
                            ->contained(false)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => $record->users()->count() > 0)
                    ->collapsible()
                    ->collapsed(fn($record) => $record->users()->count() > 10),

                // 🔐 PERMISOS POR CATEGORÍA
                Section::make('Permisos Asignados')
                    ->description('Todos los permisos agrupados por categoría')
                    ->schema([
                        // 🏠 SISTEMA
                        Section::make('Sistema y Base')
                            ->schema([
                                TextEntry::make('permissions_sistema')
                                    ->label('Permisos de Sistema')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'sistema'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success')
                                    ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'sistema'))),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'sistema'))),

                        // 🧭 NAVEGACIÓN
                        Section::make('Navegación y Menús')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_navegacion')
                                            ->label('Permisos de Navegación')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'navegacion'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'navegacion'))),

                        // 📊 DASHBOARDS
                        Section::make('Dashboards y Reportes')
                            ->schema([
                                TextEntry::make('permissions_dashboards')
                                    ->label('Permisos de Dashboards')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'dashboards'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'dashboards'))),

                        // 🎯 PROSPECTOS
                        Section::make('Prospectos')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_prospectos')
                                            ->label('Permisos de Prospectos')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'prospectos'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'prospectos'))),

                        // 👤 CLIENTES
                        Section::make('Clientes')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_clientes')
                                            ->label('Permisos de Clientes')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'clientes'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'clientes'))),

                        // 🏘️ PROPIEDADES
                        Section::make('Propiedades')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_propiedades')
                                            ->label('Permisos de Propiedades')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'propiedades'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'propiedades'))),

                        // CARTERAS
                        Section::make('Carteras')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_carteras')
                                            ->label('Permisos de Carteras')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'carteras'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'carteras'))),

                        // 💼 VENTAS
                        Section::make('Procesos de Venta')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_ventas')
                                            ->label('Permisos de Procesos de Venta')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'ventas'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'ventas'))),

                        // ⚖️ DICTÁMENES
                        Section::make('Dictámenes')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_dictamenes')
                                            ->label('Permisos de Dictámenes')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'dictamenes'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'dictamenes'))),

                        // 📂 EXPEDIENTES
                        Section::make('Expedientes Jurídicos')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_expedientes')
                                            ->label('Permisos de Expedientes Jurídicos')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'expedientes'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'expedientes'))),

                        // 🏛️ JUICIOS
                        Section::make('Juicios y Litigio')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_juicios')
                                            ->label('Permisos de Juicios y Litigio')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'juicios'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'juicios'))),

                        // 💰 PAGOS
                        Section::make('Pagos y Tesorería')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_pagos')
                                            ->label('Permisos de Pagos y Tesorería')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'pagos'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'pagos'))),

                        // 🏦 COMPRAS
                        Section::make('Procesos de Compra')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_compras')
                                            ->label('Permisos de Procesos de Compra')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'compras'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'compras'))),

                        // 📄 CONTRATOS
                        Section::make('Contratos')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_contratos')
                                            ->label('Permisos de Contratos')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'contratos'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'contratos'))),

                        // 📝 FORMALIZACIÓN
                        Section::make('Formalización y Notarías')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_formalizacion')
                                            ->label('Permisos de Formalización y Notarías')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'formalizacion'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'formalizacion'))),

                        // 🔄 CAMBIOS
                        Section::make('Cambios de Garantía')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_cambios')
                                            ->label('Permisos de Cambios de Garantía')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'cambios'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'cambios'))),

                        // 💸 DEVOLUCIONES
                        Section::make('Devoluciones')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('permissions_devoluciones')
                                            ->label('Permisos de Devoluciones')
                                            ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'devoluciones'))
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->icon('heroicon-o-check-circle')
                                            ->iconColor('success')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'devoluciones'))),

                        // ✅ VALIDACIONES
                        Section::make('Validaciones de Proceso')
                            ->schema([
                                TextEntry::make('permissions_validaciones')
                                    ->label('Permisos de Validaciones de Proceso')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'validaciones'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'validaciones'))),

                        // 👥 USUARIOS
                        Section::make('Usuarios')
                            ->schema([
                                TextEntry::make('permissions_usuarios')
                                    ->label('Permisos de Usuarios')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'usuarios'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'usuarios'))),

                        // 🔐 ROLES
                        Section::make('Roles y Permisos')
                            ->schema([
                                TextEntry::make('permissions_roles')
                                    ->label('Permisos de Roles y Permisos')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'roles'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'roles'))),

                        // 🗂️ CATÁLOGOS
                        Section::make('Catálogos')
                            ->schema([
                                TextEntry::make('permissions_catalogos')
                                    ->label('Permisos de Catálogos')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'catalogos'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'catalogos'))),

                        // 📞 ATENCIÓN
                        Section::make('Atención al Cliente')
                            ->schema([
                                TextEntry::make('permissions_atencion')
                                    ->label('Permisos de Atención al Cliente')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'atencion'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'atencion'))),

                        // 🔐 ESPECIALES
                        Section::make('Permisos Especiales')
                            ->schema([
                                TextEntry::make('permissions_especiales')
                                    ->label('Permisos Especiales')
                                    ->getStateUsing(fn($record) => self::getPermisosPorCategoria($record, 'especiales'))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->icon('heroicon-o-check-circle')
                                    ->iconColor('success'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn($record) => !empty(self::getPermisosPorCategoria($record, 'especiales'))),
                    ])
                    ->visible(fn($record) => $record->permissions()->count() > 0),

                // ⚠️ SIN PERMISOS
                Section::make('Sin Permisos')
                    ->description('Este rol no tiene permisos asignados')
                    ->schema([
                        TextEntry::make('sin_permisos')
                            ->label(false)
                            ->default('⚠️ Este rol no tiene ningún permiso asignado. Los usuarios con este rol no podrán acceder a ningún módulo del sistema.')
                            ->color('danger'),
                    ])
                    ->visible(fn($record) => $record->permissions()->count() === 0),
            ]);
    }

    /**
     * Obtiene permisos filtrados por categoría
     */
    private static function getPermisosPorCategoria($record, string $categoria): array
    {
        $prefijos = [
            'sistema' => ['ver_panel_principal', 'ver_actividad_sistema'],
            'navegacion' => ['menu_'],
            'dashboards' => ['dashboard_', 'reportes_'],
            'prospectos' => ['prospectos_'],
            'clientes' => ['clientes_'],
            'propiedades' => ['propiedades_'],
            'carteras' => ['carteras_'],
            'ventas' => ['ventas_'],
            'dictamenes' => ['dictamenes_'],
            'expedientes' => ['expedientes_'],
            'juicios' => ['juicios_'],
            'formalizacion' => ['formalizacion_'],
            'cambios' => ['cambios_'],
            'contratos' => ['contratos_'],
            'pagos' => ['pagos_'],
            'compras' => ['compras_'],
            'devoluciones' => ['devoluciones_'],
            'validaciones' => ['validaciones_'],
            'usuarios' => ['usuarios_'],
            'roles' => ['roles_'],
            'catalogos' => ['catalogos_'],
            'atencion' => ['atencion_', 'interacciones_', 'agenda_'],
            'especiales' => ['autorizar_descuentos_', 'archivos_', 'configuracion_'],
        ];

        if (!isset($prefijos[$categoria])) {
            return [];
        }

        $permissions = $record->permissions;
        $filtered = [];

        foreach ($permissions as $permission) {
            foreach ($prefijos[$categoria] as $prefijo) {
                if (str_starts_with($permission->name, $prefijo)) {
                    $filtered[] = self::formatPermissionLabel($permission->name);
                    break;
                }
            }
        }

        return $filtered;
    }

    /**
     * Formatea el nombre del permiso para mostrarlo más legible
     */
    private static function formatPermissionLabel(string $name): string
    {
        // Remueve prefijos comunes
        $label = str_replace([
            'prospectos_',
            'clientes_',
            'propiedades_',
            'carteras_',
            'ventas_',
            'dictamenes_',
            'expedientes_',
            'juicios_',
            'pagos_',
            'compras_',
            'contratos_',
            'formalizacion_',
            'cambios_',
            'devoluciones_',
            'validaciones_',
            'interacciones_',
            'agenda_',
            'archivos_',
            'usuarios_',
            'roles_',
            'catalogos_',
            'configuracion_',
            'atencion_',
            'menu_',
            'dashboard_',
            'reportes_',
            'autorizar_descuentos_'
        ], '', $name);

        // Reemplaza guiones bajos por espacios
        $label = str_replace('_', ' ', $label);

        // Capitaliza
        return ucwords($label);
    }
}
