<?php

namespace App\Filament\Resources\Juridico\SeguimientoDictamenes;

use App\Enums\EstatusAvanceEnum;
use App\Enums\EstatusDictamenEnum;
use App\Models\ActuacionDictamen;
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
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActuacionesDictamenRelationManager extends RelationManager
{
    protected static string $relationship = 'actuaciones';

    protected static ?string $title = 'Actuaciones';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function canCreate(): bool
    {
        // No permitir nuevas actuaciones si el dictamen está completado
        $seguimiento = $this->getOwnerRecord();

        if ($seguimiento->estatus === EstatusDictamenEnum::COMPLETADO) {
            return false;
        }

        return auth()->user()->can('seguimientodictamenes_editar');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('fecha_actuacion')
                ->label('Fecha de Actuación')
                ->required()
                ->default(now())
                ->native(false),

            DatePicker::make('fecha_proxima_actuacion')
                ->label('Fecha de Próxima Actuación (opcional)')
                ->native(false)
                ->nullable()
                ->helperText('Se enviará notificación recordatoria en esa fecha'),

            Textarea::make('descripcion_actuacion')
                ->label('Descripción de la Actuación')
                ->required()
                ->rows(4),

            Textarea::make('etapa_actual')
                ->label('¿Cambia la etapa? (opcional)')
                ->rows(2)
                ->helperText('Si se llena, actualiza automáticamente la etapa del dictamen'),

            FileUpload::make('archivo_evidencia')
                ->label('Archivo de Evidencia')
                ->disk('private')
                ->directory(function () {
                    return ActuacionDictamen::directorioParaDictamen(
                        $this->getOwnerRecord()->id
                    );
                })
                ->acceptedFileTypes(['application/pdf', 'application/x-pdf', 'application/octet-stream', 'image/jpeg', 'image/png'])
                ->rules(['mimes:pdf,jpg,jpeg,png'])
                ->maxSize(102400)
                ->preserveFilenames(false)
                ->helperText('PDF o imagen. Máx. 100MB.')
                ->nullable(),

            Select::make('hubo_avance')
                ->label('¿Hubo Avance?')
                ->options(EstatusAvanceEnum::class)
                ->required(),
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

                TextColumn::make('fecha_proxima_actuacion')
                    ->label('Próxima Actuación')
                    ->formatStateUsing(
                        fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '—'
                    )

                    ->color(fn($record) => $record->fecha_proxima_actuacion?->isPast() ? 'danger' : 'info'),

                TextColumn::make('semana_label')
                    ->label('Semana')
                    ->default('—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('descripcion_actuacion')
                    ->label('Actuación')
                    ->limit(80)
                    ->tooltip(fn($record) => $record->descripcion_actuacion),

                TextColumn::make('id')
                    ->label('Evidencia')
                    ->formatStateUsing(fn($state, $record) => $record->nombre_archivo ?? 'Sin archivo')
                    ->badge()
                    ->color(fn($record) => $record->archivo_evidencia ? 'info' : 'gray'),

                TextColumn::make('hubo_avance')
                    ->label('¿Avance?')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state instanceof EstatusAvanceEnum ? $state->getLabel() : $state)
                    ->color(
                        fn($record) => $record->hubo_avance instanceof EstatusAvanceEnum
                            ? $record->hubo_avance->getColor() : 'gray'
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva Actuación')
                    ->icon('heroicon-o-plus')
                    ->createAnother(false)
                    ->disabled(fn() => $this->getOwnerRecord()->estatus === EstatusDictamenEnum::COMPLETADO),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('descargar_evidencia')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn($record) => (bool) $record->archivo_evidencia)
                    ->url(fn($record) => $record->url_archivo)
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading(
                fn() => $this->getOwnerRecord()->estatus === EstatusDictamenEnum::COMPLETADO
                    ? 'Dictamen completado — no se permiten nuevas actuaciones'
                    : 'Sin actuaciones registradas'
            )
            ->emptyStateIcon('heroicon-o-document-check');
    }
}
