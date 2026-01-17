<?php

namespace App\Filament\Resources\Finanzas\ProcesoCompras\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProcesoCompraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Negociación')
                    ->schema([
                        TextInput::make('precio_compra_negociado')
                            ->label('Precio Pactado con Proveedor')
                            ->prefix('$')
                            ->numeric(),
                        TextInput::make('gastos_notariales_presupuesto')
                            ->label('Presupuesto Notarial')
                            ->prefix('$')
                            ->numeric(),
                        DatePicker::make('fecha_pago_proveedor')
                            ->label('Fecha Pago a Proveedor'),
                    ])->columns(3),

                Section::make('Formalización Notarial')
                    ->schema([
                        TextInput::make('notaria_numero')->label('No. Notaría'),
                        TextInput::make('notario_nombre')->label('Nombre Notario'),
                        TextInput::make('numero_escritura')->label('No. Escritura/Volante'),
                        DatePicker::make('fecha_firma_cesion')->label('Fecha de Firma'),
                    ])->columns(2),

                // Aquí podrías agregar el Repeater de Archivos que ya hicimos para subir la Cesión
            ]);
    }
}
