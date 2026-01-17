<?php

namespace App\Filament\Resources\Comercial\Carteras\Tables;

use App\Models\Cartera;
use App\Models\CatEstado;
use App\Models\CatMunicipio;
use App\Models\Propiedad;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarterasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('administradora.nombre')
                    ->label('Administradora')
                    ->sortable(),

                TextColumn::make('fecha_recepcion')
                    ->label('Fecha de corte')
                    ->date('d/M/Y')
                    ->sortable(),

                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PUBLICADA' => 'success',
                        'PROCESADA' => 'info',
                        'BORRADOR' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Cargado')
                    ->date('d/M/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('administradora_id')
                    ->relationship('administradora', 'nombre')
                    ->label('Por administradora'),
            ])
            ->recordAction(EditAction::class)
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Datos de la cartera')
                    ->modalWidth('2xl')
                    ->slideOver()
                    ->button()
                    ->label('Detalles'),

                Action::make('procesar_importacion')
                    ->label('Procesar archivo')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Procesar e importar propiedades?')
                    ->modalDescription('El sistema leerá el archivo CSV y creará las propiedades en la base de datos vinculadas a esta cartera y sucursal.')
                    ->visible(fn(Cartera $record) => $record->estatus === 'BORRADOR' && $record->archivo_path)
                    ->action(function (Cartera $record) {
                        // 1. Verificar archivo
                        if (!Storage::exists($record->archivo_path)) {
                            Notification::make()->danger()->title('Archivo no encontrado')->send();
                            return;
                        }

                        $path = Storage::path($record->archivo_path);
                        $file = fopen($path, 'r');

                        $header = fgetcsv($file);

                        $contador = 0;
                        $errores = 0;

                        DB::beginTransaction();

                        try {
                            while (($row = fgetcsv($file)) !== false) {
                                // 0: Codigo cartera, 1: Crédito, 2: Estado, 3: Municipio, 4: Fraccionamiento
                                // 5: Dirección, 6: Segunda dir, 7: CP, 8: Etapa Jud, 9: 2da Etapa, 10: Fecha
                                // 11: Tipo Viv, 12: M2 Const, 13: Tipo Inm, 14: Avalúo, 15: Precio Lista, 16: Cofinavit

                                // Convertir cada celda a UTF-8 real. Excel suele guardar en Windows-1252 o ISO-8859-1.
                                $row = array_map(function ($text) {
                                    return mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'UTF-8, ISO-8859-1, Windows-1252', true) ?: 'Windows-1252');
                                }, $row);

                                if (count($row) < 2) {
                                    continue;
                                }

                                $row = array_pad($row, 17, '');

                                // Limpieza de datos numéricos (quitar $ y ,)
                                $avaluo = preg_replace('/[^0-9.]/', '', $row[14]);
                                $precio = preg_replace('/[^0-9.]/', '', $row[15]);
                                $cofinavit = preg_replace('/[^0-9.]/', '', $row[16]);

                                // Búsqueda inteligente de catálogos (Estado/Municipio)
                                $estadoNombre = trim($row[2]);
                                $municipioNombre = trim($row[3]);

                                // Encontrar el ID, si no, se queda nulo y se guarda el texto en "borrador"
                                $estado = CatEstado::where('nombre', 'LIKE', "%{$estadoNombre}%")->first();
                                $municipio = CatMunicipio::where('nombre', 'LIKE', "%{$municipioNombre}%")->first();

                                $fechaJudicial = null;

                                if (!empty($row[10])) {
                                    try {
                                        $fechaJudicial = \Carbon\Carbon::createFromFormat('d/m/Y', str_replace('-', '/', trim($row[10])))->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $fechaJudicial = null;
                                    }
                                }

                                Propiedad::create([
                                    'cartera_id' => $record->id,
                                    'administradora_id' => $record->administradora_id,
                                    'sucursal_id' => $record->sucursal_id,

                                    'numero_credito' => trim($row[1]),

                                    // Ubicación
                                    'estado_id' => $estado?->id,
                                    'municipio_id' => $municipio?->id,
                                    'estado_borrador' => $estadoNombre,
                                    'municipio_borrador' => $municipioNombre,

                                    'fraccionamiento' => trim($row[4]),
                                    'direccion_completa' => trim($row[5]) . ' ' . trim($row[6]),
                                    'calle' => trim($row[5]),
                                    'codigo_postal' => trim($row[7]),

                                    // Datos Legales
                                    'etapa_judicial_reportada' => trim($row[8]) . ' - ' . trim($row[9]),
                                    'fecha_corte_judicial' => !empty($row[10]) ? date('Y-m-d', strtotime(str_replace('/', '-', $row[10]))) : null,

                                    // Características
                                    'tipo_vivienda' => trim($row[11]),
                                    'construccion_m2' => (float) $row[12],
                                    'tipo_inmueble' => trim($row[13]),

                                    // Valores
                                    'avaluo_banco' => is_numeric($avaluo) ? (float) $avaluo : 0,
                                    'precio_lista' => is_numeric($precio) ? (float) $precio : 0,
                                    'cofinavit_monto' => is_numeric($cofinavit) ? (float) $cofinavit : 0,

                                    'estatus_comercial' => 'BORRADOR',
                                    'created_by' => Auth::id(),
                                ]);

                                $contador++;
                            }

                            $record->update(['estatus' => 'PROCESADA']);

                            DB::commit();

                            if (is_resource($file)) {
                                fclose($file);
                            }

                            Notification::make()
                                ->success()
                                ->title('Procesamiento Exitoso')
                                ->body("Se importaron {$contador} propiedades correctamente.")
                                ->send();

                            $gerentes = User::where('sucursal_id', $record->sucursal_id)->role('Gerente_Sucursal')->get();

                            if ($gerentes->isNotEmpty()) {
                                Notification::make()
                                    ->title('📢 Nueva cartera por validar')
                                    ->body("Se han cargado **{$contador} propiedades** nuevas para tu sucursal en la cartera **{$record->nombre}**.\n\nPor favor, revisa los borradores y valídalos para venta.")
                                    ->warning()
                                    ->actions([
                                        Action::make('revisar')
                                            ->label('Ir a Borradores')
                                            ->button()
                                            ->url(route('filament.admin.resources.comercial.propiedades.index', [
                                                'tableFilters[estatus_comercial][value]' => 'BORRADOR'
                                            ]), shouldOpenInNewTab: true),
                                    ])
                                    ->sendToDatabase($gerentes);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();

                            if (is_resource($file)) {
                                fclose($file);
                            }

                            Notification::make()
                                ->danger()
                                ->title('Error en la Importación')
                                ->body("Error técnico: " . $e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
