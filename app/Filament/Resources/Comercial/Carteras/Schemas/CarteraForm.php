<?php

namespace App\Filament\Resources\Comercial\Carteras\Schemas;

use App\Models\CatSucursal;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CarteraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre de la cartera'),

                        Select::make('administradora_id')
                            ->relationship('administradora', 'nombre')
                            ->required()
                            ->label('Institución administradora')
                            ->native(false),

                        Select::make('sucursal_id')
                            ->label('Sucursal asignada')
                            ->options(CatSucursal::where('activo', true)->pluck('nombre', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false),

                        DatePicker::make('fecha_recepcion')
                            ->required()
                            ->label('Fecha de recepción')
                            ->default(now())
                            ->native(false),
                    ]),

                FileUpload::make('archivo_path')
                    ->label('Archivo CSV (Delimitado por comas)')
                    ->disk('local')
                    ->directory('carteras-raw')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel']) // Tipos MIME de CSV
                    ->helperText('Importante: Guarda tu Excel como "CSV (delimitado por comas)" antes de subirlo.')
                    ->hidden(function (string $operation) {
                        return $operation === 'edit';
                    }),


                    Select::make('estatus')
                    ->options([
                        'BORRADOR' => 'Borrador (Pendiente de Procesar)',
                        'PROCESADA' => 'Procesada (Lista para validar)',
                        'PUBLICADA' => 'Publicada (Inventario Visible)',
                    ])
                    ->default('BORRADOR')
                    ->disabledOn('edit')
                    ->dehydrated()
                    ->hidden(function (string $operation) {
                        return $operation === 'create';
                    }),

            ]);
    }
}
