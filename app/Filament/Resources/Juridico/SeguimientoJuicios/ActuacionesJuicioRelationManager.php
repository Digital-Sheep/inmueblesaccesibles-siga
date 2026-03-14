<?php

namespace App\Filament\Resources\Juridico\SeguimientoJuicios;

use App\Enums\EstatusAvanceEnum;
use App\Models\ActuacionJuicio;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActuacionesJuicioRelationManager extends RelationManager
{
    protected static string $relationship = 'actuaciones';

    protected static ?string $title = 'Actuaciones Semanales';

    public function canCreate(): bool
    {
        return auth()->user()->can('seguimientojuicios_editar');
    }

    /**
     * Desactivar modo read-only en ViewPage.
     * Por defecto Filament oculta todas las acciones de modificación
     * cuando el RelationManager se muestra en una ViewRecord page.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('fecha_actuacion')
                ->label('Fecha de Actuación')
                ->required()
                ->default(now())
                ->native(false),

            Textarea::make('descripcion_actuacion')
                ->label('Descripción de la Actuación')
                ->required()
                ->rows(4)
                ->helperText('Registra la evidencia o avance del caso (último acuerdo, boletín, etc.)'),

            // Disco 'private' explícito — archivos jurídicos sensibles
            // Estructura: juridico/juicios/{id_garantia}/actuaciones/{año}-{mes}/
            FileUpload::make('archivo_evidencia')
                ->label('Archivo de Evidencia / Boletín Judicial')
                ->disk('private')
                ->directory(function () {
                    $idGarantia = $this->getOwnerRecord()->id_garantia
                        ?? 'sin-garantia-' . $this->getOwnerRecord()->id;

                    return ActuacionJuicio::directorioParaJuicio($idGarantia);
                })
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(10240) // 10 MB
                ->preserveFilenames(false)
                ->helperText('PDF o imagen. Máx. 10MB. Se guarda en disco privado.')
                ->nullable(),

            Select::make('hubo_avance')
                ->label('¿Hubo Avance?')
                ->options(EstatusAvanceEnum::class)
                ->required(),

            TextInput::make('semana_label')
                ->label('Etiqueta de Semana')
                ->placeholder('SEMANA 16/02/2026')
                ->maxLength(50)
                ->helperText('Opcional — referencia visual para identificar la semana'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fecha_actuacion')
            ->defaultSort('fecha_actuacion', 'desc')
            ->columns([
                TextColumn::make('fecha_actuacion')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('semana_label')
                    ->label('Semana')
                    ->default('—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('descripcion_actuacion')
                    ->label('Actuación')
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->descripcion_actuacion),

                TextColumn::make('id')
                    ->label('Evidencia')
                    ->formatStateUsing(fn ($state, $record) => $record->nombre_archivo ?? 'Sin archivo')
                    ->badge()
                    ->color(fn ($record) => $record->archivo_evidencia ? 'info' : 'gray'),

                TextColumn::make('hubo_avance')
                    ->label('¿Avance?')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof EstatusAvanceEnum ? $state->getLabel() : $state)
                    ->color(fn ($record) => $record->hubo_avance instanceof EstatusAvanceEnum
                        ? $record->hubo_avance->getColor()
                        : 'gray'
                    ),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva Actuación')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),

                // Acción para descargar el archivo de evidencia de forma segura
                Action::make('descargar_evidencia')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn ($record) => (bool) $record->archivo_evidencia)
                    ->url(fn ($record) => $record->url_archivo)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin actuaciones registradas')
            ->emptyStateDescription('Agrega la primera actuación semanal de este juicio.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
