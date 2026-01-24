<?php

namespace App\Filament\Resources\Configuracion\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                // INFORMACIÓN BÁSICA
                Section::make('Información del Rol')
                    ->description('Nombre y descripción del rol')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Rol')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Usa snake_case: Ejemplo: SVT_Gerente_Regional')
                            ->rules(['regex:/^[a-zA-Z0-9_]+$/'])
                            ->disabled(fn($record) => in_array($record?->name, ['Super_Admin', 'DGE']))
                            ->dehydrated(),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                // PERMISOS POR PESTAÑAS
                Section::make('Permisos')
                    ->description('Selecciona los permisos que tendrá este rol')
                    ->schema([
                        Tabs::make('permisos_tabs')
                            ->statePath('permissions_data')
                            ->tabs([
                                // 🏠 BASE / SISTEMA
                                Tab::make('Sistema')
                                    ->icon('heroicon-o-home')
                                    ->schema([
                                        CheckboxList::make('sistema')
                                            ->label('Permisos de Sistema')
                                            ->options(self::getPermisosPorCategoria('sistema'))
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->bulkToggleable()
                                            ->live()
                                            ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                if ($record) {
                                                    $permisos = $record->permissions->pluck('name')->toArray();
                                                    $opciones = array_keys(self::getPermisosPorCategoria('sistema'));
                                                    $seleccionados = array_intersect($permisos, $opciones);
                                                    $component->state($seleccionados);
                                                }
                                            }),
                                    ]),

                                // 🧭 NAVEGACIÓN
                                Tab::make('Navegación')
                                    ->icon('heroicon-o-bars-3')
                                    ->schema([
                                        CheckboxList::make('navegacion')
                                            ->label('Permisos de Menús')
                                            ->options(self::getPermisosPorCategoria('navegacion'))
                                            ->columns(3)
                                            ->gridDirection('row')
                                            ->bulkToggleable()
                                            ->live()
                                            ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                if ($record) {
                                                    $permisos = $record->permissions->pluck('name')->toArray();
                                                    $opciones = array_keys(self::getPermisosPorCategoria('navegacion'));
                                                    $seleccionados = array_intersect($permisos, $opciones);
                                                    $component->state($seleccionados);
                                                }
                                            }),
                                    ]),

                                // 📊 DASHBOARDS Y REPORTES
                                Tab::make('Dashboards')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        CheckboxList::make('dashboards')
                                            ->label('Dashboards y Reportes')
                                            ->options(self::getPermisosPorCategoria('dashboards'))
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->bulkToggleable()
                                            ->live()
                                            ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                if ($record) {
                                                    $permisos = $record->permissions->pluck('name')->toArray();
                                                    $opciones = array_keys(self::getPermisosPorCategoria('dashboards'));
                                                    $seleccionados = array_intersect($permisos, $opciones);
                                                    $component->state($seleccionados);
                                                }
                                            }),
                                    ]),

                                // 💼 COMERCIAL
                                Tab::make('Comercial')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Section::make('Prospectos')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('prospectos')
                                                    ->label('Permisos de Prospectos')
                                                    ->options(self::getPermisosPorCategoria('prospectos'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('prospectos'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Clientes')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('clientes')
                                                    ->label('Permisos de Clientes')
                                                    ->options(self::getPermisosPorCategoria('clientes'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('clientes'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Propiedades')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('propiedades')
                                                    ->label('Permisos de Propiedades')
                                                    ->options(self::getPermisosPorCategoria('propiedades'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('propiedades'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Carteras')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('carteras')
                                                    ->label('Permisos de Carteras')
                                                    ->options(self::getPermisosPorCategoria('carteras'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('carteras'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Procesos de Venta')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('ventas')
                                                    ->label('Permisos de Procesos de Venta')
                                                    ->options(self::getPermisosPorCategoria('ventas'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('ventas'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Agenda')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('agenda')
                                                    ->label('Permisos de Agenda')
                                                    ->options(self::getPermisosPorCategoria('agenda'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('agenda'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Interacciones')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('interacciones')
                                                    ->label('Permisos de Interacciones')
                                                    ->options(self::getPermisosPorCategoria('interacciones'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('interacciones'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),
                                    ]),

                                // ⚖️ JURÍDICO
                                Tab::make('Jurídico')
                                    ->icon('heroicon-o-scale')
                                    ->schema([
                                        Section::make('Dictámenes')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('dictamenes')
                                                    ->label('Permisos de Dictámenes')
                                                    ->options(self::getPermisosPorCategoria('dictamenes'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('dictamenes'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Expedientes Jurídicos')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('expedientes')
                                                    ->label('Permisos de Expedientes Jurídicos')
                                                    ->options(self::getPermisosPorCategoria('expedientes'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('expedientes'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Juicios / Litigio')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('juicios')
                                                    ->label('Permisos de Juicios / Litigio')
                                                    ->options(self::getPermisosPorCategoria('juicios'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('juicios'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),
                                        Section::make('Formalización / Notarías')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('formalizacion')
                                                    ->label('Permisos de Formalización / Notarías')
                                                    ->options(self::getPermisosPorCategoria('formalizacion'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('formalizacion'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Cambios de Garantía')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('cambios')
                                                    ->label('Permisos de Cambios de Garantía')
                                                    ->options(self::getPermisosPorCategoria('cambios'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('cambios'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Contratos')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('contratos')
                                                    ->label('Permisos de Contratos')
                                                    ->options(self::getPermisosPorCategoria('contratos'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('contratos'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),
                                    ]),

                                // 💰 ADMINISTRATIVO
                                Tab::make('Administrativo')
                                    ->icon('heroicon-o-banknotes')
                                    ->schema([
                                        Section::make('Pagos')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('pagos')
                                                    ->label('Permisos de Pagos')
                                                    ->options(self::getPermisosPorCategoria('pagos'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('pagos'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Procesos de Compra')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('compras')
                                                    ->label('Permisos de Procesos de Compra')
                                                    ->options(self::getPermisosPorCategoria('compras'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('compras'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Devoluciones')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('devoluciones')
                                                    ->label('Permisos de Devoluciones')
                                                    ->options(self::getPermisosPorCategoria('devoluciones'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('devoluciones'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),
                                        Section::make('Validaciones')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('validaciones')
                                                    ->label('Permisos de Validaciones')
                                                    ->options(self::getPermisosPorCategoria('validaciones'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('validaciones'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Archivos')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('archivos')
                                                    ->label('Permisos de Archivos')
                                                    ->options(self::getPermisosPorCategoria('archivos'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('archivos'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),
                                    ]),

                                // ⚙️ CONFIGURACIÓN
                                Tab::make('Configuración')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->schema([
                                        Section::make('Usuarios')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('usuarios')
                                                    ->label('Permisos de Usuarios')
                                                    ->options(self::getPermisosPorCategoria('usuarios'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('usuarios'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Roles')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('roles')
                                                    ->label('Permisos de Roles')
                                                    ->options(self::getPermisosPorCategoria('roles'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('roles'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Catálogos')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('catalogos')
                                                    ->label('Permisos de Catálogos')
                                                    ->options(self::getPermisosPorCategoria('catalogos'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('catalogos'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),

                                        Section::make('Sistema')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                CheckboxList::make('configuracion')
                                                    ->label('Permisos de Configuración')
                                                    ->options(self::getPermisosPorCategoria('configuracion'))
                                                    ->columns(3)
                                                    ->gridDirection('row')
                                                    ->bulkToggleable()
                                                    ->live()
                                                    ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                        if ($record) {
                                                            $permisos = $record->permissions->pluck('name')->toArray();
                                                            $opciones = array_keys(self::getPermisosPorCategoria('configuracion'));
                                                            $seleccionados = array_intersect($permisos, $opciones);
                                                            $component->state($seleccionados);
                                                        }
                                                    }),
                                            ]),
                                    ]),

                                // 📞 ATENCIÓN AL CLIENTE
                                Tab::make('Atención al Cliente')
                                    ->icon('heroicon-o-phone')
                                    ->schema([
                                        CheckboxList::make('atencion')
                                            ->label('Permisos de Atención al Cliente')
                                            ->options(self::getPermisosPorCategoria('atencion'))
                                            ->columns(3)
                                            ->gridDirection('row')
                                            ->bulkToggleable()
                                            ->live()
                                            ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                if ($record) {
                                                    $permisos = $record->permissions->pluck('name')->toArray();
                                                    $opciones = array_keys(self::getPermisosPorCategoria('atencion'));
                                                    $seleccionados = array_intersect($permisos, $opciones);
                                                    $component->state($seleccionados);
                                                }
                                            }),
                                    ]),

                                // 🔐 ESPECIALES
                                Tab::make('Permisos Especiales')
                                    ->icon('heroicon-o-key')
                                    ->schema([
                                        CheckboxList::make('especiales')
                                            ->label('Descuentos y Permisos Especiales')
                                            ->options(self::getPermisosPorCategoria('especiales'))
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->bulkToggleable()
                                            ->live()
                                            ->afterStateHydrated(function (CheckboxList $component, $record) {
                                                if ($record) {
                                                    $permisos = $record->permissions->pluck('name')->toArray();
                                                    $opciones = array_keys(self::getPermisosPorCategoria('especiales'));
                                                    $seleccionados = array_intersect($permisos, $opciones);
                                                    $component->state($seleccionados);
                                                }
                                            }),
                                    ]),
                            ])
                            ->contained(false)
                            ->persistTabInQueryString(),
                    ]),
            ]);
    }

    /**
     * Obtiene permisos filtrados por categoría
     */
    private static function getPermisosPorCategoria(string $categoria): array
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
            'agenda' => ['agenda_'],
            'interacciones' => ['interacciones_'],
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
            'archivos' => ['archivos_'],
            'usuarios' => ['usuarios_'],
            'roles' => ['roles_'],
            'catalogos' => ['catalogos_'],
            'configuracion' => ['configuracion_'],
            'atencion' => ['atencion_'],
            'especiales' => ['autorizar_descuentos_'],
        ];

        if (!isset($prefijos[$categoria])) {
            return [];
        }

        $permissions = Permission::all();
        $filtered = [];

        foreach ($permissions as $permission) {
            foreach ($prefijos[$categoria] as $prefijo) {
                if (str_starts_with($permission->name, $prefijo)) {
                    $filtered[$permission->name] = self::formatPermissionLabel($permission->name);
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
