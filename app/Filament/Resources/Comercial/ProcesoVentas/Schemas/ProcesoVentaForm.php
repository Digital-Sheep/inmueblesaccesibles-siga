<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Schemas;

use App\Models\Cliente;
use App\Models\Propiedad;
use App\Models\Prospecto;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProcesoVentaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                MorphToSelect::make('interesado')
                    ->label('¿Quién compra?')
                    ->types([
                        Type::make(Prospecto::class)
                            ->titleAttribute('nombre_completo')
                            ->label('Prospecto')
                            ->modifyOptionsQueryUsing(function (Builder $query) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                // Aplicar filtros de prospectos
                                if ($user->can('prospectos_ver_todos')) {
                                    return $query;
                                }

                                if ($user->sucursal_id !== null) {
                                    $query->where('sucursal_id', $user->sucursal_id);
                                }

                                if (!$user->can('prospectos_ver_sucursal_completa')) {
                                    $query->where('usuario_responsable_id', $user->id);
                                }

                                return $query;
                            }),
                        Type::make(Cliente::class)
                            ->titleAttribute('nombres')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nombres} {$record->apellido_paterno}")
                            ->label('Cliente Existente')
                            ->modifyOptionsQueryUsing(function (Builder $query) {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();

                                // Aplicar filtros de clientes
                                if ($user->can('clientes_ver_todos')) {
                                    return $query;
                                }

                                if ($user->sucursal_id !== null) {
                                    $query->where('sucursal_id', $user->sucursal_id);
                                }

                                if (!$user->can('clientes_ver_sucursal_completa')) {
                                    $query->where('usuario_responsable_id', $user->id);
                                }

                                return $query;
                            }),
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
                    ->live(),

                Select::make('vendedor_id')
                    ->relationship('vendedor', 'name')
                    ->default(fn() => Auth::id())
                    ->required()
                    ->disabled(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return ! $user->hasRole(['Super_Admin', 'Gerente_Sucursal']);
                    }),

                Select::make('estatus')
                    ->options([
                        'ACTIVO' => 'En Negociación',
                        'VISITA_REALIZADA' => 'Visita Realizada',
                        'SOLICITUD_APARTADO' => 'Solicitando Apartado',
                        'APARTADO' => 'Apartado Confirmado',
                        'CANCELADO' => 'Cancelado',
                    ])
                    ->default('ACTIVO')
                    ->disabled(function (string $operation) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return ! $user->hasRole(['Super_Admin', 'Gerente_Sucursal']) || $operation === 'create';
                    })
                    ->hidden(function (string $operation) {
                        return $operation === 'create';
                    })
                    ->dehydrated(),

                TextEntry::make('created_at')
                    ->label('Fecha de Inicio')
                    ->date('M j, Y')
                    ->placeholder('Sin definir')
                    ->hidden(fn(string $operation) => $operation === 'create'),
            ]);
    }
}
