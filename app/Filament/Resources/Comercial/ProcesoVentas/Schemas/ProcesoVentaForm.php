<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Schemas;

use App\Models\Propiedad;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;

use Illuminate\Support\Facades\Auth;

class ProcesoVentaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        // COLUMNA IZQUIERDA: QUIÉN Y QUÉ
                        Section::make('Datos de la Venta')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                // Selector Polimórfico: Permite buscar en Prospectos Y Clientes
                                MorphToSelect::make('interesado')
                                    ->label('¿Quién compra?')
                                    ->types([
                                        Type::make(\App\Models\Prospecto::class)
                                            ->titleAttribute('nombre_completo')
                                            ->label('Prospecto'),
                                        MorphToSelect\Type::make(\App\Models\Cliente::class)
                                            ->titleAttribute('nombres') // O el virtual si funciona en búsqueda
                                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nombres} {$record->apellido_paterno}")
                                            ->label('Cliente Existente'),
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('propiedad_id')
                                    ->label('Propiedad')
                                    ->relationship(
                                        'propiedad',
                                        'numero_credito',
                                        fn($query) =>
                                        $query->whereIn('estatus_comercial', ['DISPONIBLE', 'EN_REVISION'])
                                    )
                                    ->getOptionLabelFromRecordUsing(fn(Propiedad $record) => $record->nombre_corto)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(), // Si cambia, podríamos mostrar el precio abajo
                            ])->columnSpan(2),

                        // COLUMNA DERECHA: ESTATUS Y VENDEDOR
                        Section::make('Control Interno')
                            ->schema([
                                Select::make('vendedor_id')
                                    ->relationship('vendedor', 'name')
                                    ->default(fn() => Auth::id())
                                    ->required(),

                                Select::make('estatus')
                                    ->options([
                                        'ACTIVO' => 'En Negociación',
                                        'VISITA_REALIZADA' => 'Visita Realizada',
                                        'SOLICITUD_APARTADO' => 'Solicitando Apartado',
                                        'APARTADO' => 'Apartado Confirmado',
                                        'CANCELADO' => 'Cancelado',
                                    ])
                                    ->default('ACTIVO')
                                    // El estatus idealmente se mueve por botones de acción (Flujo),
                                    // pero lo dejamos editable para admins.
                                    ->disabled(function () {
                                        /** @var \App\Models\User $user */
                                        $user = Auth::user();

                                        return ! $user->hasRole(['Super_Admin', 'Gerente_Sucursal']);
                                    })
                                    ->dehydrated(),

                                Placeholder::make('creado')
                                    ->label('Fecha de Inicio')
                                    ->content(fn($record) => $record?->created_at?->format('d/m/Y') ?? now()->format('d/m/Y')),
                            ])->columnSpan(1),
                    ]),
            ]);
    }
}
