<?php

namespace App\Filament\Resources\Comercial\Prospectos\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Propiedad;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;

class ProcesosVentaRelationManager extends RelationManager
{
    protected static string $relationship = 'procesosVenta'; // Asegúrate que esta relación exista en Prospecto.php
    protected static ?string $title = 'Procesos de Venta Activos';
    protected static string|BackedEnum|null $icon = 'heroicon-o-home-modern';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('propiedad_id')
                    ->label('Seleccionar Propiedad')
                    ->options(
                        Propiedad::where('estatus_comercial', 'DISPONIBLE')
                            ->get()
                            ->mapWithKeys(fn($p) => [$p->id => "{$p->numero_credito} - {$p->direccion_completa}"])
                    )
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                // Campos iniciales ocultos
                Hidden::make('vendedor_id')
                    ->default(fn() => Auth::id()),
                Hidden::make('estatus')
                    ->default('ACTIVO'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('propiedad.numero_credito')->label('Propiedad')->weight('bold'),
                TextColumn::make('estatus')->badge(),
                TextColumn::make('created_at')->date()->label('Iniciado'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Iniciar Nueva Venta')
                    ->modalHeading('Asociar Propiedad al Prospecto')
                    ->successNotificationTitle('Proceso iniciado correctamente'),
            ])
            ->actions([
                // Botón para ir a gestionar el proceso completo
                Action::make('gestionar')
                    ->label('Gestionar Flujo')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->url(fn($record) => route('filament.admin.resources.comercial.proceso-ventas.view', $record)),
            ]);
    }
}
