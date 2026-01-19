<?php

namespace App\Filament\Resources\Comercial\ProcesoVentas\Tables;

use App\Models\Dictamen;
use App\Models\ProcesoVenta;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\RawJs;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProcesoVentasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Folio')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('interesado_id')
                    ->label('Cliente / Prospecto')
                    ->formatStateUsing(function (ProcesoVenta $record) {
                        if ($record->interesado_type === 'App\\Models\\Prospecto') {
                            return $record->interesado->nombre_completo . " (Prospecto)";
                        }

                        return $record->interesado->nombres . " " . $record->interesado->apellido_paterno . " (Cliente)";
                    })
                    ->icon('heroicon-m-user')
                    ->weight('bold'),

                TextColumn::make('propiedad.numero_credito')
                    ->label('Propiedad')
                    ->description(fn(ProcesoVenta $record) => Str::limit($record->propiedad->direccion_completa, 40))
                    ->searchable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ACTIVO', 'VISITA_REALIZADA' => 'info',
                        'SOLICITUD_APARTADO' => 'warning',
                        'APARTADO', 'DICTAMINADO_R2' => 'success',
                        'CANCELADO' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('vendedor.name')
                    ->label('Vendedor')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->date('d/M/Y')
                    ->label('Iniciado')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->options([
                        'ACTIVO' => 'En Negociación',
                        'APARTADO' => 'Apartados',
                        'CANCELADO' => 'Caídas',
                    ]),

                SelectFilter::make('vendedor_id')
                    ->relationship('vendedor', 'name')
                    ->label('Vendedor'),
            ])
            ->recordAction(ViewAction::class)
            ->recordActions([
                Action::make('subir_aviso')
                    ->label('Subir Aviso de privacidad')
                    ->icon('heroicon-o-shield-check')
                    ->button()
                    ->color('primary')
                    ->visible(function (ProcesoVenta $record) {
                        return $record->estatus === 'ACTIVO' &&
                            !$record->interesado->archivos()->where('categoria', 'AVISO_PRIVACIDAD')->exists();
                    })
                    ->schema([
                        FileUpload::make('archivo_temporal')
                            ->label('Documento Firmado')
                            ->disk('public')
                            ->directory('legal/avisos')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->required(),
                    ])
                    ->action(function (ProcesoVenta $record, array $data) {
                        $record->interesado->archivos()->create([
                            'categoria'       => 'AVISO_PRIVACIDAD',
                            'ruta_archivo'    => $data['archivo_temporal'],
                            'nombre_original' => 'Aviso_Privacidad_' . $record->interesado->nombre_completo . '.pdf',
                            'tipo_mime'       => 'application/pdf',
                            'subido_por_id'   => Auth::id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Aviso vinculado al prospecto')
                            ->send();
                    }),

                Action::make('registrar_visita')
                    ->label('Registrar visita')
                    ->icon('heroicon-o-map-pin')
                    ->button()
                    ->color('warning')
                    ->visible(fn(ProcesoVenta $record) => $record->estatus === 'ACTIVO' && $record->interesado->archivos()->where('categoria', 'AVISO_PRIVACIDAD')->exists())
                    ->requiresConfirmation()
                    ->modalHeading('¿Confirmar visita?')
                    ->modalDescription('Confirma que el cliente ya visitó la propiedad física.')
                    ->action(function (ProcesoVenta $record) {
                        $record->update(['estatus' => 'VISITA_REALIZADA']);
                        Notification::make()->success()->title('Visita registrada')->send();
                    }),

                Action::make('generar_contrato')
                    ->label('Generar contrato')
                    ->icon('heroicon-o-document-text')
                    ->button()
                    ->visible(fn(ProcesoVenta $record) => $record->estatus === 'VISITA_REALIZADA')
                    ->action(function (ProcesoVenta $record) {
                        if (empty($record->folio_apartado)) {
                            $record->update(['folio_apartado' => 'APT-' . time()]);
                        }

                        $record->update(['estatus' => 'APARTADO_GENERADO']);

                        return redirect()->route('generar.contrato.apartado', $record);
                    }),

                Action::make('subir_contrato_firmado')
                    ->label('Subir contrato firmado')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->color('warning')
                    ->visible(
                        fn(ProcesoVenta $record) =>
                        $record->estatus === 'APARTADO_GENERADO' &&
                            !$record->archivos()->where('categoria', 'CONTRATO_APARTADO_FIRMADO')->exists()
                    )
                    ->schema([
                        FileUpload::make('contrato_firmado')
                            ->label('Contrato escaneado (Firmado)')
                            ->directory('legal/contratos')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required(),
                    ])
                    ->action(function (ProcesoVenta $record, array $data) {
                        $record->archivos()->create([
                            'categoria'       => 'CONTRATO_APARTADO_FIRMADO',
                            'ruta_archivo'    => $data['contrato_firmado'],
                            'nombre_original' => 'Contrato_Apartado_Firmado.pdf',
                            'tipo_mime'       => 'application/pdf',
                            'subido_por_id'   => Auth::id(),
                        ]);

                        // Notificar a UCP
                        // $juridico = \App\Models\User::role(['Abogado', 'Gerente Legal'])->get();

                        // if ($juridico->isNotEmpty()) {
                        //     Notification::make()
                        //         ->title('Contrato Firmado')
                        //         ->body("Se ha subido el contrato firmado del folio {$record->folio_apartado}. Pueden iniciar gestión.")
                        //         ->sendToDatabase($juridico);
                        // }

                        // Notification::make()->success()->title('Contrato guardado')->send();
                    }),

                Action::make('subir_pago')
                    ->label('Subir pago')
                    ->icon('heroicon-o-currency-dollar')
                    ->button()
                    ->color('success')
                    ->visible(
                        fn(ProcesoVenta $record) => ($record->estatus === 'APARTADO_GENERADO' && $record->archivos()->where('categoria', 'CONTRATO_APARTADO_FIRMADO')->exists()) ||
                            $record->pagos()->where('estatus', 'RECHAZADO')->latest()->first()?->estatus === 'RECHAZADO'
                    )
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('monto')->numeric()->required()->prefix('$')
                                // Colocar monto por defecto del apartado
                                ->default(10000)
                                // Formatear visualmente para moneda
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),

                            Select::make('metodo_pago')
                                ->options(['TRANSFERENCIA' => 'Transferencia', 'EFECTIVO' => 'Efectivo'])
                                ->required()
                                ->native(false),
                        ]),

                        FileUpload::make('comprobante_url')
                            ->directory('pagos')
                            ->required()
                            ->label('Comprobante de pago'),
                    ])
                    ->action(function ($record, $data) {
                        // Crear el registro del Pago
                        $record->pagos()->create([
                            'concepto' => 'APARTADO',
                            'monto' => $data['monto'],
                            'metodo_pago' => $data['metodo_pago'],
                            'comprobante_url' => $data['comprobante_url'],
                            'estatus' => 'PENDIENTE',
                        ]);

                        $record->update(['estatus' => 'APARTADO_POR_VALIDAR']);

                        Notification::make()
                            ->success()
                            ->title('Comprobante Subido')
                            ->body('El pago ha sido enviado a revisión por Contabilidad.')
                            ->send();

                        // NOTIFICAR A CONTABILIDAD
                        // $contadores = User::role(['Contador', 'Administrador', 'Gerente Administrativo'])->get();

                        // if ($contadores->isNotEmpty()) {
                        //     Notification::make()
                        //         ->title('Nuevo pago por validar')
                        //         ->body("Se ha subido un comprobante de apartado por $ " . number_format($data['monto']) . " para la propiedad {$record->propiedad->numero_credito}.\n\nFavor de verificar ingreso en bancos.")
                        //         ->warning() // Color amarillo/naranja para llamar la atención
                        //         ->actions([
                        //             Action::make('validar')
                        //                 ->label('Ir a validar')
                        //                 ->button()
                        //                 ->url(route('filament.admin.resources.finanzas.pagos.index', [
                        //                     'tableFilters[estatus][value]' => 'PENDIENTE'
                        //                 ]), shouldOpenInNewTab: true),
                        //         ])
                        //         ->sendToDatabase($contadores);
                        // }


                        // NOTIFICAR A JURÍDICO
                        // $juridico = \App\Models\User::role(['Abogado', 'Gerente Legal'])->get();

                        // if ($juridico->isNotEmpty()) {
                        //     Notification::make()
                        //         ->title('Expediente en proceso')
                        //         ->body("Se ha pagado el apartado de {$record->propiedad->numero_credito}.\n\nAcción requerida: Solicitar CLG y antecedentes registrales.")
                        //         ->warning()
                        //         ->sendToDatabase($juridico);
                        // }

                        // Notification::make()->success()->title('Pago enviado y áreas notificadas')->send();
                    })
                    ->modalWidth('lg'),

                Action::make('solicitar_dictamen')
                    ->label('Solicitar dictamen')
                    ->icon('heroicon-o-scale')
                    ->button()
                    ->color('info')
                    ->visible(fn(ProcesoVenta $record) => in_array($record->estatus, ['APARTADO_VALIDADO']))
                    ->schema([
                        Section::make('Información para jurídico')
                            ->description('Captura los datos preliminares requeridos para iniciar la investigación.')
                            ->schema([
                                TextInput::make('nombre_proveedor')
                                    ->label('Nombre del proveedor / Dueño')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('numero_credito')
                                    ->label('Número de crédito (Si aplica)')
                                    ->default(fn(ProcesoVenta $record) => $record->propiedad->numero_credito),

                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('es_dueno_real')
                                            ->label('¿El proveedor es el dueño real?')
                                            ->required(),

                                        Toggle::make('tiene_posesion')
                                            ->label('¿Tiene posesión física?')
                                            ->required(),
                                    ]),

                                DatePicker::make('fecha_ultimo_pago_deudor')
                                    ->label('Fecha último pago (Si se conoce)'),

                                Textarea::make('observaciones')
                                    ->label('Notas para jurídico')
                                    ->rows(3),
                            ])
                    ])
                    ->action(function (ProcesoVenta $record, array $data) {
                        Dictamen::create([
                            'tipo_solicitud' => 'VENTA',
                            'origen_solicitud' => 'CARTERA',
                            'proceso_venta_id' => $record->id,
                            'propiedad_id' => $record->propiedad_id,
                            'usuario_solicitante_id' => Auth::id(),

                            'nombre_proveedor' => $data['nombre_proveedor'],
                            'numero_credito' => $data['numero_credito'],
                            'es_dueno_real' => $data['es_dueno_real'],
                            'tiene_posesion' => $data['tiene_posesion'],
                            'fecha_ultimo_pago_deudor' => $data['fecha_ultimo_pago_deudor'],

                            'estatus' => 'PENDIENTE',
                        ]);

                        // Actualizar el proceso de venta
                        $record->update([
                            'estatus' => 'EN_DICTAMINACION',
                        ]);

                        // Notificar
                        Notification::make()
                            ->success()
                            ->title('Solicitud enviada')
                            ->body('El expediente ha sido enviado a la bandeja de jurídico.')
                            ->send();
                    }),

                Action::make('subir_enganche')
                    ->label('Subir enganche (Cierre)')
                    ->icon('heroicon-o-banknotes')
                    ->button()
                    ->color('success')
                    ->visible(fn(ProcesoVenta $record) => $record->estatus === 'DICTAMINADO_R2')
                    ->schema([
                        Section::make('Registro de liquidación')
                            ->description('Sube el comprobante del pago complementario para la cesión.')
                            ->schema([
                                TextInput::make('monto')
                                    ->label('Monto restante')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),

                                Select::make('metodo_pago')
                                    ->options([
                                        'TRANSFERENCIA' => 'Transferencia electrónica',
                                        'CHEQUE' => 'Cheque certificado',
                                        'DEPOSITO' => 'Depósito bancario',
                                    ])
                                    ->required(),

                                FileUpload::make('comprobante_url')
                                    ->label('Ficha de depósito / transferencia')
                                    ->directory('pagos/enganches')
                                    ->image()
                                    ->required(),

                                Textarea::make('notas')
                                    ->label('Observaciones para contabilidad')
                                    ->rows(2),
                            ])
                    ])
                    ->action(function (ProcesoVenta $record, array $data) {
                        // Registrar el pago
                        $record->pagos()->create([
                            'concepto' => 'ENGANCHE', // O 'LIQUIDACION'
                            'monto' => $data['monto'],
                            'metodo_pago' => $data['metodo_pago'],
                            'comprobante_url' => $data['comprobante_url'],
                            'estatus' => 'PENDIENTE',
                        ]);

                        // Actualizar estatus
                        $record->update(['estatus' => 'ESPERANDO_ENGANCHE']);

                        // Notificar a contabilidad
                        // $contadores = \App\Models\User::role(['Contador', 'Administrador', 'Gerente Administrativo'])->get();

                        // if ($contadores->isNotEmpty()) {
                        //     Notification::make()
                        //         ->title('Enganche por validar')
                        //         ->body("Se ha subido la liquidación por **$ " . number_format($data['monto']) . "** del cliente **{$record->interesado->nombre_completo}**.\n\nPropiedad: {$record->propiedad->numero_credito}")
                        //         ->warning()
                        //         ->actions([
                        //             Action::make('validar')
                        //                 ->button()
                        //                 ->url(route('filament.admin.resources.finanzas.pagos.index', ['tableFilters[estatus][value]' => 'PENDIENTE']), shouldOpenInNewTab: true),
                        //         ])
                        //         ->sendToDatabase($contadores);
                        // }

                        Notification::make()->success()->title('Enganche Enviado a Validación')->send();
                    })
            ])
            ->defaultSort('created_at', 'desc');
    }
}
