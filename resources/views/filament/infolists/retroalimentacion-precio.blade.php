<div style="display: flex; flex-direction: column; gap: 16px;">
    @forelse($getRecord()->aprobacionesPrecio as $aprobacion)
        <div
            style="border-radius: 8px; border: 1px solid
            @if ($aprobacion->estatus === 'APROBADO') #bbf7d0; background-color: #f0fdf4;
            @elseif($aprobacion->estatus === 'RECHAZADO') #fecaca; background-color: #fef2f2;
            @else #fef08a; background-color: #fefce8; @endif
            padding: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 600; color: #111827;">
                        {{ $aprobacion->tipo_aprobador === 'COMERCIAL' ? '🏢 Comercial' : '💰 Contabilidad' }}
                    </span>
                    <span
                        style="font-size: 0.875rem; padding: 4px 8px; border-radius: 9999px;
                        @if ($aprobacion->estatus === 'APROBADO') background-color: #dcfce7; color: #166534;
                        @elseif($aprobacion->estatus === 'RECHAZADO') background-color: #fee2e2; color: #991b1b;
                        @else background-color: #fef9c3; color: #854d0e; @endif">
                        @if ($aprobacion->estatus === 'APROBADO')
                            ✅ Aprobado
                        @elseif($aprobacion->estatus === 'RECHAZADO')
                            ❌ Rechazado
                        @else
                            ⏳ Pendiente
                        @endif
                    </span>
                </div>

                @if ($aprobacion->fecha_respuesta)
                    <span style="font-size: 0.875rem; color: #6b7280;">
                        {{ $aprobacion->fecha_respuesta->diffForHumans() }}
                    </span>
                @endif
            </div>

            <div style="margin-bottom: 8px;">
                <span style="font-size: 0.875rem; color: #4b5563;">Precio evaluado:</span>
                <span style="font-weight: bold; color: #111827;">
                    ${{ number_format($aprobacion->precio_propuesto, 2) }}
                </span>
            </div>

            @if ($aprobacion->estatus === 'RECHAZADO' && $aprobacion->precio_sugerido_alternativo)
                <div
                    style="margin-bottom: 8px; background-color: #ffffff; border-radius: 4px; padding: 8px; border: 1px solid #fca5a5;">
                    <span style="font-size: 0.875rem; color: #b91c1c; font-weight: 500;">💡 Precio sugerido:</span>
                    <span style="font-weight: bold; color: #7f1d1d;">
                        ${{ number_format($aprobacion->precio_sugerido_alternativo, 2) }}
                    </span>
                    <span style="font-size: 0.875rem; color: #dc2626;">
                        ({{ number_format((($aprobacion->precio_sugerido_alternativo - $aprobacion->precio_propuesto) / $aprobacion->precio_propuesto) * 100, 2) }}%)
                    </span>
                </div>
            @endif

            @if ($aprobacion->comentarios)
                <div
                    style="margin-top: 12px; padding: 12px; background-color: #ffffff; border-radius: 4px; border: 1px solid #e5e7eb;">
                    <div style="font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 4px;">💬
                        Comentarios:</div>
                    <p style="font-size: 0.875rem; color: #4b5563; margin: 0;">{{ $aprobacion->comentarios }}</p>
                </div>
            @endif

            @if ($aprobacion->aprobador)
                <div style="margin-top: 8px; font-size: 0.75rem; color: #6b7280;">
                    Por: {{ $aprobacion->aprobador->name }}
                </div>
            @endif
        </div>
    @empty
        <div style="text-align: center; padding: 32px 0; color: #6b7280;">
            <p>⏳ Aún no hay retroalimentación de las áreas</p>
        </div>
    @endforelse
</div>
