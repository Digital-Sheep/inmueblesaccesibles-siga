<?php

namespace App\Filament\Resources\Juridico\Dictamens\Tables;

use App\Models\Dictamen;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DictamensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha de solicitud')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Días transcurridos')
                    ->formatStateUsing(fn(Dictamen $record) => $record->dias_transcurridos . ' días')
                    ->badge()
                    ->color(fn(Dictamen $record): string => match (true) {
                        $record->estatus === 'TERMINADO' => 'gray',
                        $record->dias_transcurridos > 20 => 'danger',
                        $record->dias_transcurridos > 10 => 'warning',
                        default => 'success',
                    })
                    ->description(fn(Dictamen $record) => 'Solicitado el ' . $record->created_at->format('d/m/Y')),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'EN_REVISION' => 'info',
                        'TERMINADO' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('unidad_responsable')
                    ->badge()
                    ->colors([
                        'info' => 'UCP',
                        'warning' => 'URRJ',
                        'danger' => 'DIL',
                    ]),

                TextColumn::make('tipo_solicitud')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('propiedad.direccion_completa')
                    ->label('Propiedad')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('solicitante.name')
                    ->label('Solicitó'),
            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->options([
                        'PENDIENTE' => 'Pendientes',
                        'EN_REVISION' => 'En revisión',
                        'TERMINADO' => 'Terminados',
                    ]),

                SelectFilter::make('unidad_responsable')
                    ->options(['UCP' => 'UCP (Ventas)', 'URRJ' => 'URRJ (Cambios)', 'DIL' => 'DIL (Inversiones)']),
            ])
            ->recordActions([
                // SOLICITUD DE DOCUMENTOS
                Action::make('registrar_solicitud_docs')
                    ->label('Solicitar Documentos')
                    ->icon('heroicon-o-paper-airplane')
                    ->button()
                    ->color('info')
                    // Solo visible si está pendiente y aún no se piden los docs
                    ->visible(fn(Dictamen $record) => $record->estatus !== 'TERMINADO' && in_array($record->sub_etapa, ['POR_INICIAR']))
                    ->schema([
                        DatePicker::make('fecha_solicitud_documentos')
                            ->label('Fecha de Solicitud')
                            ->default(now())
                            ->native(false)
                            ->required(),

                        Select::make('documento_esperado')
                            ->label('¿Qué se solicitó?')
                            ->options([
                                'EXPEDIENTE_BANCO' => 'Expediente a la Administradora',
                                'CLG_RPPC' => 'CLG / Antecedentes (Registro Público)',
                                'AMBOS' => 'Ambos',
                            ])
                            ->native(false)
                            ->required(),

                        Textarea::make('observaciones')
                            ->label('Detalles de la gestión')
                            ->placeholder('Se envió correo a Zendere / Se ingresó trámite en RPPC folio...'),
                    ])
                    ->action(function (Dictamen $record, array $data) {
                        $record->update([
                            'sub_etapa' => 'SOLICITUD_ENVIADA',
                            'fecha_solicitud_documentos' => $data['fecha_solicitud_documentos'],
                            'documento_esperado' => $data['documento_esperado'],
                            'estatus' => 'EN_REVISION',
                            'sub_etapa' => 'SOLICITUD_ENVIADA',
                        ]);

                        Notification::make()->success()->title('Solicitud registrada')->body('Solicitud registrada (15 días aprox).')->send();
                    }),

                // ANALISIS CRÉDITO
                Action::make('analisis_credito')
                    ->label('Validar datos')
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->button()
                    ->color('warning')
                    // Visible si está en revisión y aún no se ha validado por dirección
                    ->visible(fn(Dictamen $record) => $record->estatus === 'EN_REVISION' && !$record->fecha_analisis_credito)
                    ->schema([
                        // Identidad del Activo (Corrección de Datos)
                        Wizard::make([
                            Step::make('Identidad del activo')
                                ->description('Verifica que los datos de la garantía coincidan con la realidad jurídica y las carteras.')
                                ->schema([
                                    Grid::make(2)->schema([
                                        // Validación de Dirección
                                        TextInput::make('direccion_original')
                                            ->label('Dirección actual')
                                            ->disabled()
                                            ->default(fn(Dictamen $r) => $r->propiedad->direccion_completa),
                                        Group::make([
                                            Toggle::make('direccion_correcta')->label('¿Correcta?')->default(true)->live(),
                                            Textarea::make('direccion_corregida')->required()->visible(fn(Get $get) => !$get('direccion_correcta')),
                                        ]),

                                        // Validación de Crédito
                                        TextInput::make('credito_original')
                                            ->label('No. Crédito actual')
                                            ->disabled()
                                            ->default(fn(Dictamen $r) => $r->propiedad->numero_credito),
                                        Group::make([
                                            Toggle::make('credito_correcto')->label('¿Correcto?')->default(true)->live(),
                                            TextInput::make('numero_credito_corregido')->required()->visible(fn(Get $get) => !$get('credito_correcto')),
                                        ]),
                                    ]),
                                ]),

                            // Radiografía Financiera
                            Step::make('Análisis financiero')
                                ->description('Datos económicos de la deuda para valuar la compra.')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('monto_demandado_original')
                                            ->label('Suerte principal (Monto demandado)')
                                            ->prefix('$')
                                            ->numeric(),

                                        TextInput::make('mensualidades_vencidas')
                                            ->label('Mensualidades vencidas')
                                            ->numeric()
                                            ->suffix('meses'),

                                        TextInput::make('valor_cuota_mensual')
                                            ->label('Valor cuota mensual')
                                            ->prefix('$')
                                            ->numeric(),

                                        TextInput::make('intereses_anuales_estimados')
                                            ->label('Intereses anuales (%)')
                                            ->suffix('%')
                                            ->numeric(),
                                    ]),
                                ]),
                        ])->submitAction(new HtmlString('<button type="submit" class="fi-btn ...">Guardar análisis</button>')),
                    ])
                    ->action(function (Dictamen $record, array $data) {
                        // Guardar el análisis en el Dictamen
                        $record->update([
                            'direccion_correcta' => $data['direccion_correcta'],
                            'direccion_corregida' => $data['direccion_corregida'] ?? null,
                            'credito_correcto' => $data['credito_correcta'],
                            'numero_credito_corregido' => $data['numero_credito_corregido'] ?? null,

                            'monto_demandado_original' => $data['monto_demandado_original'],
                            'mensualidades_vencidas' => $data['mensualidades_vencidas'],
                            'valor_cuota_mensual' => $data['valor_cuota_mensual'],

                            'fecha_analisis_credito' => now(),
                            'validado_por_director_id' => $data['validar_como_director'] ? Auth::id() : null,
                        ]);

                        // SI EL DIRECTOR VALIDÓ -> CORREGIR LA PROPIEDAD MAESTRA
                        if ($data['validar_como_director']) {
                            $propiedad = $record->propiedad;
                            $cambios = [];

                            if (!$data['direccion_correcta']) {
                                $cambios['direccion_completa'] = $data['direccion_corregida'];
                            }
                            if (!$data['credito_correcta']) {
                                $cambios['numero_credito'] = $data['numero_credito_corregido'];
                            }
                            // if (!$data['administradora_correcta']) {
                            //     $cambios['administradora_id'] = $data['administradora_corregida_id'];
                            // }

                            if (!empty($cambios)) {
                                $propiedad->update($cambios);

                                Notification::make()
                                    ->title('Propiedad Actualizada')
                                    ->body('Se han corregido los datos maestros de la propiedad según el análisis jurídico.')
                                    ->success()
                                    ->send();
                            }
                        } else {
                            Notification::make()
                                ->title('Análisis registrado')
                                ->body('Pendiente de validación final por Dirección Jurídica.')
                                ->success()
                                ->send();
                        }
                    }),

                // ACCIÓN 3: CONFIRMAR RECEPCIÓN
                Action::make('confirmar_recepcion')
                    ->label('Subir documentos')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->button()
                    ->color('success')
                    ->visible(fn(Dictamen $record) => $record->sub_etapa === 'SOLICITUD_ENVIADA')
                    ->schema([
                        DatePicker::make('fecha_recepcion_documentos')
                            ->label('Fecha de recepción')
                            ->default(now())
                            ->required(),

                        Repeater::make('archivos_recibidos')
                            ->relationship('archivos')
                            ->schema([
                                FileUpload::make('ruta_archivo')
                                    ->label('Documento escaneado')
                                    ->directory('juridico/expedientes')
                                    ->required(),
                                Hidden::make('categoria')->default('EXPEDIENTE_JURIDICO'),
                                Hidden::make('subido_por_id')->default(Auth::id()),
                            ])
                            ->addActionLabel('Agregar documento'),
                    ])
                    ->action(function (Dictamen $record, array $data) {
                        $record->update([
                            'sub_etapa' => 'DOCUMENTOS_RECIBIDOS', // Listo para dictaminar
                            'fecha_recepcion_documentos' => $data['fecha_recepcion_documentos'],
                        ]);

                        Notification::make()->success()->title('Expediente completo')->body('Ahora puedes proceder a realizar el dictamen.')->send();
                    }),

                // ACCIÓN 4: DICTAMINAR
                EditAction::make('dictaminar')
                    ->label('Emitir Dictamen Final')
                    ->icon('heroicon-o-gavel')
                    ->color('danger')
                    // Solo se habilita si ya tenemos los documentos (o si es un caso que no requería solicitud externa)
                    ->visible(
                        fn(Dictamen $record) =>
                        $record->estatus !== 'TERMINADO' &&
                            $record->fecha_analisis_credito &&
                            ($record->sub_etapa === 'DOCUMENTOS_RECIBIDOS' || $record->origen_solicitud === 'INTERNO')
                    )
                    ->form([
                        // SECCIÓN A: REGISTRAL
                        Section::make('Hallazgos Registrales (RPPC)')
                            ->schema([
                                TextInput::make('folio_real_rppc')->label('Folio Real'),
                                Toggle::make('dictamen_registral_concluido')->default(true),
                                Repeater::make('gravamenes_detectados')
                                    ->schema([
                                        Select::make('tipo')->options(['HIPOTECA' => 'Hipoteca', 'EMBARGO' => 'Embargo', 'OTRO' => 'Otro']),
                                        TextInput::make('acreedor'),
                                        TextInput::make('monto')->numeric()->prefix('$'),
                                    ])->columns(3)->defaultItems(0),
                            ]),

                        // SECCIÓN B: VEREDICTO
                        Section::make('Veredicto Final')
                            ->schema([
                                Select::make('estatus')
                                    ->options(['TERMINADO' => 'Dictamen Concluido'])
                                    ->default('TERMINADO')->disabled()->dehydrated(),
                                Select::make('resultado_final')
                                    ->options(['POSITIVO' => '✅ Positivo (R2)', 'NEGATIVO' => '❌ Negativo (R1)', 'CAMBIO' => '🔄 Requiere Cambio'])->required(),
                                Select::make('nomenclatura_generada')
                                    ->options(['R2' => 'R2', 'R1' => 'R1', 'RB' => 'RB', 'RV' => 'RV', 'R-INV' => 'R-INV'])->required(),
                                RichEditor::make('observaciones_finales')->required()->columnSpanFull(),
                            ]),
                    ]),

                // AGREGAR NOTIFICACIÓN A TODOS LOS INVOLUCRADOS
            ])
            ->defaultSort('created_at', 'desc');
    }
}
