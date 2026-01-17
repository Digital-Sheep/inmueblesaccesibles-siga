<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Schemas;

use App\Models\Propiedad;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
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
                    ->hidden(fn (string $operation) => $operation === 'create'),
            ]);
    }
}
