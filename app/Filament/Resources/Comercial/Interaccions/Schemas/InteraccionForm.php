<?php

namespace App\Filament\Resources\Comercial\Interaccions\Schemas;

use App\Models\Prospecto;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InteraccionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Interacción')
                    ->schema([
                        // 1. Lógica Polimórfica: Definimos el Tipo de Entidad (Por defecto Prospecto)
                        Hidden::make('entidad_type')
                            ->default(Prospecto::class),

                        // 2. Selector de Prospecto (Pre-llenado desde URL)
                        Select::make('entidad_id')
                            ->label('Prospecto')
                            ->options(Prospecto::limit(50)->pluck('nombre_completo', 'id')) // Optimización básica
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search) => Prospecto::where('nombre_completo', 'like', "%{$search}%")->limit(50)->pluck('nombre_completo', 'id'))
                            ->getOptionLabelUsing(fn($value): ?string => Prospecto::find($value)?->nombre_completo)
                            ->default(request()->query('prospecto_id')) // <--- TOMA EL ID DE LA URL
                            ->disabled(fn() => request()->has('prospecto_id')) // Bloquea si viene pre-llenado
                            ->dehydrated() // Envía el dato aunque esté deshabilitado
                            ->required(),

                        // 3. Tipo de Interacción
                        Select::make('tipo')
                            ->options([
                                'LLAMADA' => '📞 Llamada',
                                'WHATSAPP' => '📱 WhatsApp',
                                'CORREO' => '📧 Correo',
                                'VISITA_SUCURSAL' => '🏢 Visita Sucursal',
                                'VISITA_PROPIEDAD' => '🏡 Visita Propiedad',
                                'NOTA_INTERNA' => '📝 Nota Interna',
                            ])
                            ->default(fn() => request()->query('tipo_interaccion', 'LLAMADA')) // Default desde URL o Llamada
                            ->required(),

                        // 4. Resultado (Si ya ocurrió)
                        Select::make('resultado')
                            ->options([
                                'CONTACTADO' => '✅ Contactado',
                                'BUZON' => 'voicemail Buzón',
                                'CITA_AGENDADA' => '📅 Cita Agendada',
                                'NO_INTERESA' => '❌ No Interesa',
                                'SIN_RESPUESTA' => '🔕 Sin Respuesta',
                            ]),

                        // 5. Fechas
                        DateTimePicker::make('fecha_programada')
                            ->label('Agendar para')
                            ->minDate(now())
                            ->default(null),

                        DateTimePicker::make('fecha_realizada')
                            ->label('Fecha Realización')
                            ->default(now())
                            ->required(),

                        // 6. Comentarios
                        Textarea::make('comentario')
                            ->required()
                            ->columnSpanFull(),

                        // 7. Usuario (Automático)
                        Hidden::make('usuario_id')
                            ->default(fn() => Auth::id()),

                        // --- CAMPOS DE VENTA CRUZADA (OCULTOS) ---

                        // Marca si es venta cruzada basado en la URL
                        Hidden::make('es_venta_cruzada')
                            ->default(fn() => request()->query('origen') === 'venta_cruzada'),

                        // Busca quién es el dueño original si es venta cruzada
                        Hidden::make('propietario_original_id')
                            ->default(function () {
                                if (request()->query('origen') === 'venta_cruzada' && request()->has('prospecto_id')) {
                                    return Prospecto::find(request()->query('prospecto_id'))?->usuario_responsable_id;
                                }
                                return null;
                            }),
                    ])->columns(2),
            ]);
    }
}
