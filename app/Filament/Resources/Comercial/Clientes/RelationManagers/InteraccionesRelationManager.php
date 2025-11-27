<?php

namespace App\Filament\Resources\Comercial\ClienteResource\RelationManagers;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

use Filament\Tables\Columns\TextColumn;

class InteraccionesRelationManager extends RelationManager
{
    protected static string $relationship = 'interacciones';

    // 👇 LA LÍNEA VITAL (Polimorfismo)
    protected static ?string $inverseRelationship = 'entidad';

    protected static ?string $title = 'Bitácora de Seguimiento';
    protected static string|BackedEnum|null $icon = 'heroicon-o-clipboard-document-list';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'LLAMADA' => '📞 Llamada',
                                'WHATSAPP' => '💬 WhatsApp',
                                'EMAIL' => '✉️ Correo',
                                'VISITA_SUCURSAL' => '🏢 Visita Sucursal',
                                'VISITA_PROPIEDAD' => '🏠 Visita Propiedad',
                                'NOTA_INTERNA' => '📝 Nota Interna',
                                // Agregamos un tipo específico para Clientes
                                'NOTIFICACION_LEGAL' => '⚖️ Notificación Legal',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('resultado')
                            ->label('Estatus')
                            ->options([
                                'CONTACTADO' => '✅ Contactado',
                                'BUZON' => '📭 Buzón / No contestó',
                                'CITA_AGENDADA' => '📅 Cita Agendada',
                                'SIN_RESPUESTA' => '❓ Sin respuesta',
                                'DOCUMENTOS_RECIBIDOS' => '📂 Documentos Recibidos',
                            ])
                            ->required()
                            ->native(false),
                    ]),

                DateTimePicker::make('fecha_realizada')
                    ->label('Fecha y Hora')
                    ->default(now())
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('comentario')
                    ->label('Detalle')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

                Hidden::make('usuario_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_realizada')
                    ->label('Fecha')
                    ->dateTime('d/M/Y h:i A')
                    ->sortable()
                    ->width('15%'),

                TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LLAMADA' => 'info',
                        'NOTIFICACION_LEGAL' => 'danger', // Destacamos lo legal
                        'WHATSAPP' => 'success',
                        'VISITA_PROPIEDAD' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('resultado')
                    ->badge(),

                TextColumn::make('comentario')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->comentario)
                    ->wrap(),

                TextColumn::make('usuario.name')
                    ->label('Registró')
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar Seguimiento')
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_realizada', 'desc');
    }
}
