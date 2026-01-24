@php
    $record = $getRecord();

    $etapas = [
        ['key' => 'ACTIVO', 'label' => 'Negociación', 'emoji' => '💬'],
        ['key' => 'VISITA_REALIZADA', 'label' => 'Visita', 'emoji' => '🏠'],
        ['key' => 'APARTADO_VALIDADO', 'label' => 'Apartado', 'emoji' => '✅'],
        ['key' => 'DICTAMINADO_POSITIVO', 'label' => 'Dictamen', 'emoji' => '⚖️'],
        ['key' => 'ENGANCHE_PAGADO', 'label' => 'Enganche', 'emoji' => '💰'],
        ['key' => 'COMPRA_FINALIZADA', 'label' => 'Compra', 'emoji' => '🏢'],
        ['key' => 'LIQUIDACION_PAGADA', 'label' => 'Liquidación', 'emoji' => '💵'],
        ['key' => 'ESCRITURADO', 'label' => 'Escrituración', 'emoji' => '📝'],
        ['key' => 'ENTREGADO', 'label' => 'Entregado', 'emoji' => '🎉'],
    ];

    $estatusMap = [
        'ACTIVO' => 0,
        'VISITA_PROGRAMADA' => 0,
        'VISITA_REALIZADA' => 1,
        'APARTADO_GENERADO' => 2,
        'APARTADO_POR_VALIDAR' => 2,
        'APARTADO_VALIDADO' => 2,
        'EN_DICTAMINACION' => 3,
        'DICTAMINADO_POSITIVO' => 3,
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

    $etapaActual = $estatusMap[$record->estatus] ?? 0;
    $esCancelado = $record->estatus === 'CANCELADO';
@endphp

<div style="padding: 8px 0;">
    @foreach ($etapas as $index => $etapa)
        @php
            $completada = !$esCancelado && $index < $etapaActual;
            $actual = !$esCancelado && $index === $etapaActual;
            $pendiente = !$esCancelado && $index > $etapaActual;
        @endphp

        <div
            style="display: flex; align-items: flex-start; gap: 16px; position: relative; padding-bottom: {{ $loop->last ? '0' : '24px' }};">
            {{-- Línea conectora vertical --}}
            @if (!$loop->last)
                <div
                    style="position: absolute; left: 20px; top: 48px; width: 2px; height: calc(100% - 24px); background-color: {{ $completada || $actual ? '#3b82f6' : '#e5e7eb' }};">
                </div>
            @endif

            {{-- Círculo con icono/emoji --}}
            <div style="position: relative; flex-shrink: 0; z-index: 10;">
                <div
                    style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; border: 2px solid;
                    {{ $completada ? 'background-color: #10b981; border-color: #10b981; box-shadow: 0 1px 2px rgba(0,0,0,0.05);' : '' }}
                    {{ $actual ? 'background-color: #3b82f6; border-color: #3b82f6; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2), 0 0 0 4px rgba(59, 130, 246, 0.1);' : '' }}
                    {{ $pendiente ? 'background-color: white; border-color: #d1d5db;' : '' }}
                    {{ $esCancelado ? 'background-color: #f3f4f6; border-color: #d1d5db;' : '' }}
                ">
                    @if ($completada)
                        <svg style="width: 20px; height: 20px; color: white;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    @elseif($actual)
                        <div style="width: 12px; height: 12px; background-color: white; border-radius: 50%;"></div>
                    @else
                        <div style="width: 8px; height: 8px; background-color: #9ca3af; border-radius: 50%;"></div>
                    @endif
                </div>
            </div>

            {{-- Contenido de la etapa --}}
            <div style="flex: 1; margin-top: -2px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">{{ $etapa['emoji'] }}</span>
                    <p
                        style="margin: 0; font-size: 14px; font-weight: {{ $actual ? '700' : '600' }};
                        color: {{ $completada ? '#059669' : ($actual ? '#2563eb' : ($esCancelado ? '#9ca3af' : '#6b7280')) }};">
                        {{ $etapa['label'] }}
                    </p>
                </div>

                <div style="margin-top: 4px;">
                    @if ($actual)
                        <span
                            style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; font-size: 11px; font-weight: 500; border-radius: 9999px; background-color: #dbeafe; color: #1e40af;">
                            <svg style="width: 12px; height: 12px;" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="3" />
                            </svg>
                            En proceso
                        </span>
                    @elseif($completada)
                        <span
                            style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; font-size: 11px; font-weight: 500; border-radius: 9999px; background-color: #d1fae5; color: #065f46;">
                            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Completado
                        </span>
                    @else
                        <span style="font-size: 11px; color: #9ca3af;">
                            Pendiente
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
