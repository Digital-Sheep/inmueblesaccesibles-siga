{{-- resources/views/filament/modals/actuaciones-rapidas.blade.php --}}
{{-- Modal de lectura rápida — últimas 5 actuaciones — estilos en línea --}}

<div style="display: flex; flex-direction: column; gap: 12px;">
    @foreach ($actuaciones as $actuacion)
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; gap: 8px;">

            {{-- Header: fecha + semana + badge avance --}}
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <span style="font-size: 14px; font-weight: 600; color: #111827;">
                    {{ \Carbon\Carbon::parse($actuacion->fecha_actuacion)->format('d/m/Y') }}
                </span>

                <div style="display: flex; align-items: center; gap: 8px;">
                    @if ($actuacion->semana_label)
                        <span style="font-size: 11px; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 9999px;">
                            {{ $actuacion->semana_label }}
                        </span>
                    @endif

                    @php
                        $badgeStyles = match ($actuacion->hubo_avance?->value ?? '') {
                            'SI'                => 'color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0;',
                            'NO'                => 'color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca;',
                            'EN_ESPERA_ACUERDO' => 'color: #92400e; background: #fffbeb; border: 1px solid #fde68a;',
                            default             => 'color: #374151; background: #f3f4f6; border: 1px solid #e5e7eb;',
                        };
                    @endphp

                    <span style="font-size: 11px; font-weight: 500; padding: 2px 8px; border-radius: 9999px; {{ $badgeStyles }}">
                        {{ $actuacion->hubo_avance?->getLabel() ?? '—' }}
                    </span>
                </div>
            </div>

            {{-- Descripción --}}
            <p style="font-size: 13px; color: #374151; margin: 0; line-height: 1.5;">
                {{ $actuacion->descripcion_actuacion }}
            </p>

            {{-- Etapa (si cambió) --}}
            @if ($actuacion->etapa_actual)
                <p style="font-size: 12px; color: #6b7280; margin: 0; font-style: italic;">
                    Etapa: {{ $actuacion->etapa_actual }}
                </p>
            @endif

            {{-- Archivo adjunto --}}
            @if ($actuacion->archivo_evidencia)
                <p style="font-size: 12px; color: #2563eb; margin: 0;">
                    📎 {{ $actuacion->nombre_archivo }}
                </p>
            @endif
        </div>
    @endforeach

    {{-- Link a expediente completo --}}
    <div style="padding-top: 12px; border-top: 1px solid #e5e7eb;">
        <a href="{{ $verTodoUrl }}"
           style="font-size: 13px; color: #7c3aed; font-weight: 500; text-decoration: none;">
            Ver expediente completo →
        </a>
    </div>
</div>
