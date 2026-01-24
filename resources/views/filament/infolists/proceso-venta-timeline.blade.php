@php
    $record = $getRecord();

    $etapas = [
        ['key' => 'ACTIVO', 'label' => 'Negociación', 'icon' => 'heroicon-m-chat-bubble-left-right'],
        ['key' => 'VISITA_REALIZADA', 'label' => 'Visita', 'icon' => 'heroicon-m-home'],
        ['key' => 'APARTADO_VALIDADO', 'label' => 'Apartado', 'icon' => 'heroicon-m-document-check'],
        ['key' => 'DICTAMINADO_POSITIVO', 'label' => 'Dictamen', 'icon' => 'heroicon-m-scale'],
        ['key' => 'ENGANCHE_PAGADO', 'label' => 'Enganche', 'icon' => 'heroicon-m-currency-dollar'],
        ['key' => 'COMPRA_FINALIZADA', 'label' => 'Compra', 'icon' => 'heroicon-m-building-office-2'],
        ['key' => 'LIQUIDACION_PAGADA', 'label' => 'Liquidación', 'icon' => 'heroicon-m-banknotes'],
        ['key' => 'ESCRITURADO', 'label' => 'Escrituración', 'icon' => 'heroicon-m-document-text'],
        ['key' => 'ENTREGADO', 'label' => 'Entregado', 'icon' => 'heroicon-m-check-circle'],
    ];

    $estatusActual = $record->estatus;

    // Mapeo de estatus a índice de etapa
    $estatusMap = [
        'ACTIVO' => 0,
        'VISITA_PROGRAMADA' => 0,
        'VISITA_REALIZADA' => 1,
        'APARTADO_GENERADO' => 2,
        'APARTADO_POR_VALIDAR' => 2,
        'APARTADO_VALIDADO' => 2,
        'EN_DICTAMINACION' => 3,
        'DICTAMINADO_POSITIVO' => 3,
        'DICTAMINADO_NEGATIVO' => 3,
        'ENGANCHE_SOLICITADO' => 4,
        'ENGANCHE_POR_VALIDAR' => 4,
        'ENGANCHE_PAGADO' => 4,
        'EN_PROCESO_COMPRA' => 5,
        'COMPRA_FINALIZADA' => 5,
        'LIQUIDACION_SOLICITADA' => 6,
        'LIQUIDACION_POR_VALIDAR' => 6,
        'LIQUIDACION_PAGADA' => 6,
        'EN_ESCRITURACION' => 7,
        'ESCRITURADO' => 7,
        'ENTREGA_PROGRAMADA' => 8,
        'ENTREGADO' => 8,
        'CANCELADO' => -1,
    ];

    $etapaActual = $estatusMap[$estatusActual] ?? 0;
    $esCancelado = $estatusActual === 'CANCELADO';
@endphp

<div class="space-y-6">
    {{-- Barra de progreso --}}
    <div class="relative">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t-2 {{ $esCancelado ? 'border-red-300' : 'border-gray-300' }}"></div>
        </div>
        <div class="absolute inset-0 flex items-center" aria-hidden="true"
            style="width: {{ $esCancelado ? 0 : ($etapaActual / (count($etapas) - 1)) * 100 }}%">
            <div class="w-full border-t-2 border-primary-600"></div>
        </div>
    </div>

    {{-- Etapas --}}
    <div class="flex justify-between -mt-3">
        @foreach ($etapas as $index => $etapa)
            @php
                $completada = !$esCancelado && $index < $etapaActual;
                $actual = !$esCancelado && $index === $etapaActual;
                $pendiente = !$esCancelado && $index > $etapaActual;
            @endphp

            <div class="flex flex-col items-center" style="flex: 1">
                {{-- Círculo --}}
                <div
                    class="relative flex items-center justify-center w-10 h-10 rounded-full border-2
                    @if ($completada) bg-primary-600 border-primary-600
                    @elseif($actual)
                        bg-white border-primary-600 ring-4 ring-primary-100
                    @elseif($esCancelado)
                        bg-gray-200 border-gray-300
                    @else
                        bg-white border-gray-300 @endif
                ">
                    @if ($completada)
                        <x-filament::icon icon="heroicon-m-check" class="w-5 h-5 text-white" />
                    @elseif($actual)
                        <x-filament::icon :icon="$etapa['icon']" class="w-5 h-5 text-primary-600" />
                    @else
                        <x-filament::icon :icon="$etapa['icon']" class="w-5 h-5 text-gray-400" />
                    @endif
                </div>

                {{-- Label --}}
                <div class="mt-2 text-center">
                    <p
                        class="text-xs font-medium
                        @if ($completada) text-primary-600
                        @elseif($actual)
                            text-primary-700 font-bold
                        @elseif($esCancelado)
                            text-gray-400
                        @else
                            text-gray-500 @endif
                    ">
                        {{ $etapa['label'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Estado actual descriptivo --}}
    <div
        class="mt-6 p-4 rounded-lg {{ $esCancelado ? 'bg-red-50 border border-red-200' : 'bg-blue-50 border border-blue-200' }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if ($esCancelado)
                    <x-filament::icon icon="heroicon-m-x-circle" class="w-6 h-6 text-red-600" />
                @else
                    <x-filament::icon icon="heroicon-m-information-circle" class="w-6 h-6 text-blue-600" />
                @endif
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium {{ $esCancelado ? 'text-red-800' : 'text-blue-800' }}">
                    Estado actual:
                    <span class="font-bold">
                        {{ str_replace('_', ' ', $estatusActual) }}
                    </span>
                </p>
                <p class="mt-1 text-sm {{ $esCancelado ? 'text-red-700' : 'text-blue-700' }}">
                    @if ($esCancelado)
                        Este proceso ha sido cancelado. Motivo:
                        {{ $record->motivo_cancelacion ? str_replace('_', ' ', $record->motivo_cancelacion) : 'No especificado' }}
                    @else
                        @switch($estatusActual)
                            @case('ACTIVO')
                                El proceso está en negociación inicial. Siguiente paso: Registrar visita a la propiedad.
                            @break

                            @case('VISITA_REALIZADA')
                                Visita realizada. Siguiente paso: Generar contrato de apartado.
                            @break

                            @case('APARTADO_GENERADO')
                                Contrato generado. Esperando firma y pago del apartado.
                            @break

                            @case('APARTADO_POR_VALIDAR')
                                Pago de apartado en validación por Contabilidad.
                            @break

                            @case('APARTADO_VALIDADO')
                                Apartado validado. Siguiente paso: Solicitar dictamen jurídico.
                            @break

                            @case('EN_DICTAMINACION')
                                En proceso de dictaminación jurídica. Esperando resultado del área legal.
                            @break

                            @case('DICTAMINADO_POSITIVO')
                                Dictamen aprobado. Siguiente paso: Solicitar enganche al cliente.
                            @break

                            @case('ENGANCHE_SOLICITADO')
                                Enganche solicitado. Esperando pago del cliente.
                            @break

                            @case('ENGANCHE_POR_VALIDAR')
                                Pago de enganche en validación.
                            @break

                            @case('ENGANCHE_PAGADO')
                                Enganche pagado. Proceso de compra de propiedad iniciado.
                            @break

                            @case('EN_PROCESO_COMPRA')
                                En proceso de compra. GAD gestionando adquisición de la propiedad.
                            @break

                            @case('COMPRA_FINALIZADA')
                                Propiedad adquirida. Siguiente paso: Solicitar liquidación al cliente.
                            @break

                            @case('LIQUIDACION_SOLICITADA')
                                Liquidación solicitada. Esperando pago final del cliente.
                            @break

                            @case('LIQUIDACION_POR_VALIDAR')
                                Pago de liquidación en validación.
                            @break

                            @case('LIQUIDACION_PAGADA')
                                Liquidación completada. Siguiente paso: Iniciar escrituración a nombre del cliente.
                            @break

                            @case('EN_ESCRITURACION')
                                En proceso de escrituración. Jurídico gestionando trámite notarial.
                            @break

                            @case('ESCRITURADO')
                                Escritura lista. Siguiente paso: Programar entrega física.
                            @break

                            @case('ENTREGA_PROGRAMADA')
                                Entrega programada para {{ $record->fecha_entrega_programada?->format('d/m/Y') }}.
                            @break

                            @case('ENTREGADO')
                                ¡Proceso completado! Propiedad entregada exitosamente.
                            @break

                            @default
                                Proceso en curso.
                        @endswitch
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
