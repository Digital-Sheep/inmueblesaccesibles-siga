{{-- resources/views/filament/components/conflicto-aprobaciones.blade.php --}}

<div style="background: #fef3c7; border: 2px solid #f59e0b; border-radius: 8px; padding: 16px;">

    <div style="font-weight: 700; font-size: 1.125rem; margin-bottom: 16px; color: #92400e;">
        Conflicto de aprobaciones
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">

        {{-- COMERCIAL --}}
        <div
            style="background: {{ $comercial->estatus === 'APROBADO' ? '#f0fdf4' : '#fef2f2' }};
                    border: 2px solid {{ $comercial->estatus === 'APROBADO' ? '#22c55e' : '#ef4444' }};
                    border-radius: 8px;
                    padding: 12px;">

            <div style="font-weight: 600; font-size: 1rem; margin-bottom: 8px; color: #111827;">
                🏢 COMERCIAL
            </div>

            <div style="font-size: 0.875rem; line-height: 1.5;">
                <div style="margin-bottom: 6px;">
                    <strong>Estado:</strong>
                    <span
                        style="color: {{ $comercial->estatus === 'APROBADO' ? '#15803d' : '#b91c1c' }}; font-weight: 700;">
                        {{ $comercial->estatus === 'APROBADO' ? '✅ APROBADO' : '❌ RECHAZADO' }}
                    </span>
                </div>

                @if ($comercial->precio_sugerido_alternativo)
                    <div style="margin-bottom: 6px; background: #fef3c7; padding: 6px; border-radius: 4px;">
                        <strong>Precio Sugerido:</strong><br>
                        <span style="font-size: 1.125rem; font-weight: 700; color: #92400e;">
                            ${{ number_format($comercial->precio_sugerido_alternativo, 2) }}
                        </span>
                    </div>
                @endif

                @if ($comercial->comentarios)
                    <div
                        style="margin-top: 8px; padding: 8px; background: #ffffff; border-radius: 4px; border-left: 3px solid #9ca3af;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 4px;">
                            Comentarios:
                        </div>
                        <div style="color: #374151; font-style: italic;">
                            {{ Str::limit($comercial->comentarios, 120) }}
                        </div>
                    </div>
                @endif

                @if ($comercial->aprobador)
                    <div style="margin-top: 6px; font-size: 0.75rem; color: #6b7280;">
                        Por: {{ $comercial->aprobador->name }}
                    </div>
                @endif
            </div>
        </div>

        {{-- CONTABILIDAD --}}
        <div
            style="background: {{ $contabilidad->estatus === 'APROBADO' ? '#f0fdf4' : '#fef2f2' }};
                    border: 2px solid {{ $contabilidad->estatus === 'APROBADO' ? '#22c55e' : '#ef4444' }};
                    border-radius: 8px;
                    padding: 12px;">

            <div style="font-weight: 600; font-size: 1rem; margin-bottom: 8px; color: #111827;">
                📊 CONTABILIDAD
            </div>

            <div style="font-size: 0.875rem; line-height: 1.5;">
                <div style="margin-bottom: 6px;">
                    <strong>Estado:</strong>
                    <span
                        style="color: {{ $contabilidad->estatus === 'APROBADO' ? '#15803d' : '#b91c1c' }}; font-weight: 700;">
                        {{ $contabilidad->estatus === 'APROBADO' ? '✅ APROBADO' : '❌ RECHAZADO' }}
                    </span>
                </div>

                @if ($contabilidad->precio_sugerido_alternativo)
                    <div style="margin-bottom: 6px; background: #fef3c7; padding: 6px; border-radius: 4px;">
                        <strong>Precio Sugerido:</strong><br>
                        <span style="font-size: 1.125rem; font-weight: 700; color: #92400e;">
                            ${{ number_format($contabilidad->precio_sugerido_alternativo, 2) }}
                        </span>
                    </div>
                @endif

                @if ($contabilidad->comentarios)
                    <div
                        style="margin-top: 8px; padding: 8px; background: #ffffff; border-radius: 4px; border-left: 3px solid #9ca3af;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 4px;">
                            Comentarios:
                        </div>
                        <div style="color: #374151; font-style: italic;">
                            {{ Str::limit($contabilidad->comentarios, 120) }}
                        </div>
                    </div>
                @endif

                @if ($contabilidad->aprobador)
                    <div style="margin-top: 6px; font-size: 0.75rem; color: #6b7280;">
                        Por: {{ $contabilidad->aprobador->name }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PRECIO ORIGINAL --}}
    <div
        style="margin-top: 12px; padding: 12px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 600; color: #1e40af;">
                💰 Precio Original Cotizado:
            </span>
            <span style="font-size: 1.25rem; font-weight: 700; color: #1e3a8a;">
                ${{ number_format($precioOriginal, 2) }}
            </span>
        </div>
    </div>

    {{-- MENSAJE INFORMATIVO --}}
    <div
        style="margin-top: 12px; padding: 8px; background: #ffffff; border-radius: 4px; font-size: 0.875rem; color: #6b7280; text-align: center;">
        ℹ️ Como DGE, tu decisión resolverá este conflicto de manera definitiva
    </div>
</div>
