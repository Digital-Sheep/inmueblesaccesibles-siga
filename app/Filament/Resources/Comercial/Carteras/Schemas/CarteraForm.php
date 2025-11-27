<?php

namespace App\Filament\Resources\Comercial\Carteras\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CarteraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Carga de Cartera')
                    ->description('Registre los datos del archivo maestro proporcionado por la Administradora.')
                    ->icon('heroicon-o-document-arrow-up')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nombre del Lote/Cartera'),

                                Select::make('administradora_id')
                                    ->relationship('administradora', 'nombre')
                                    ->required()
                                    ->label('Institución Administradora'),
                            ]),

                        DatePicker::make('fecha_recepcion')
                            ->required()
                            ->label('Fecha de Corte/Recepción')
                            ->default(now()),

                        // Campo para subir el archivo de Excel/CSV
                        FileUpload::make('archivo_path')
                            ->label('Archivo de Cartera (Excel/CSV)')
                            ->disk('local') // Guarda en el storage/app/
                            ->directory('carteras-raw') // Carpeta específica
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv']),

                        // El estatus solo se actualiza al validar o procesar, no por el formulario
                        Select::make('estatus')
                            ->options([
                                'BORRADOR' => 'Borrador (Pendiente de Procesar)',
                                'VALIDADA' => 'Validada (Lista para publicar)',
                                'PUBLICADA' => 'Publicada (Inventario Visible)',
                            ])
                            ->default('BORRADOR')
                            ->disabledOn('edit') // Evita que se cambie el estado a mano
                            ->dehydrated(),
                    ]),
            ]);
    }
}
