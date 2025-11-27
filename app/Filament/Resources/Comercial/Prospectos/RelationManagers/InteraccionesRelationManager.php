<?php

namespace App\Filament\Resources\Comercial\ProspectoResource\RelationManagers;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class InteraccionesRelationManager extends RelationManager
{
    protected static string $relationship = 'interacciones';

    // Le dice a Filament que use la columna 'entidad_id' y 'entidad_type'
    protected static ?string $inverseRelationship = 'entidad';

    protected static ?string $title = 'Bitácora de Interacciones';
    protected static string|BackedEnum|null $icon = 'heroicon-o-chat-bubble-left-right';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo de Interacción')
                            ->options([
                                'LLAMADA' => '📞 Llamada',
                                'WHATSAPP' => '💬 WhatsApp',
                                'EMAIL' => '✉️ Correo',
                                'VISITA_SUCURSAL' => '🏢 Visita a Sucursal',
                                'VISITA_PROPIEDAD' => '🏠 Visita a Propiedad',
                                'NOTA_INTERNA' => '📝 Nota Interna',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('resultado')
                            ->label('Resultado')
                            ->options([
                                'CONTACTADO' => '✅ Contactado / Exitoso',
                                'BUZON' => '📭 Buzón / No contestó',
                                'CITA_AGENDADA' => '📅 Cita Agendada',
                                'NO_INTERESA' => '⛔ No le interesa',
                                'SIN_RESPUESTA' => '❓ Sin respuesta',
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
                    ->label('Detalle / Resumen')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull()
                    ->placeholder('Escribe aquí qué se habló con el prospecto...'),

                // Guardamos automáticamente quién registró la interacción
                Forms\Components\Hidden::make('usuario_id')
                    ->default(fn() => Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tipo')
            ->columns([
                Tables\Columns\TextColumn::make('fecha_realizada')
                    ->label('Fecha')
                    ->dateTime('d/M/Y h:i A')
                    ->sortable()
                    ->width('15%'),

                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'LLAMADA' => 'info',
                        'WHATSAPP' => 'success',
                        'VISITA_PROPIEDAD', 'VISITA_SUCURSAL' => 'primary',
                        'NOTA_INTERNA' => 'gray',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('resultado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'CONTACTADO', 'CITA_AGENDADA' => 'success',
                        'NO_INTERESA' => 'danger',
                        'BUZON', 'SIN_RESPUESTA' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('comentario')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->comentario)
                    ->wrap(), // Permite que el texto baje de línea si es muy largo

                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Asesor')
                    ->icon('heroicon-m-user')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                // Filtro rápido para ver solo lo importante
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'LLAMADA' => 'Llamadas',
                        'CITA_AGENDADA' => 'Citas',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar Interacción')
                    ->modalHeading('Registrar Nueva Interacción')
                    ->modalWidth('lg')
                    ->slideOver(), // Hace que salga como panel lateral (más moderno)
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
            ->defaultSort('fecha_realizada', 'desc'); // Lo más reciente primero
    }
}
