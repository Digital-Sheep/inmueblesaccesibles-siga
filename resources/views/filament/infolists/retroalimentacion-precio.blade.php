<div class="space-y-4">
    @forelse($getRecord()->aprobacionesPrecio as $aprobacion)
        <div
            class="rounded-lg border p-4
            @if ($aprobacion->estatus === 'APROBADO') bg-green-50 border-green-200
            @elseif($aprobacion->estatus === 'RECHAZADO') bg-red-50 border-red-200
            @else bg-yellow-50 border-yellow-200 @endif
        ">
            <!-- Header -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-900">
                        {{ $aprobacion->tipo_aprobador === 'COMERCIAL' ? '🏢 Comercial' : '💰 Contabilidad' }}
                    </span>
                    <span
                        class="text-sm px-2 py-1 rounded-full
                        @if ($aprobacion->estatus === 'APROBADO') bg-green-100 text-green-800
                        @elseif($aprobacion->estatus === 'RECHAZADO') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif
                    ">
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
                    <span class="text-sm text-gray-500">
                        {{ $aprobacion->fecha_respuesta->diffForHumans() }}
                    </span>
                @endif
            </div>

            <!-- Precio Propuesto -->
            <div class="mb-2">
                <span class="text-sm text-gray-600">Precio evaluado:</span>
                <span class="font-bold text-gray-900">
                    ${{ number_format($aprobacion->precio_propuesto, 2) }}
                </span>
            </div>

            <!-- Precio Sugerido Alternativo (si rechazó) -->
            @if ($aprobacion->estatus === 'RECHAZADO' && $aprobacion->precio_sugerido_alternativo)
                <div class="mb-2 bg-white rounded p-2 border border-red-300">
                    <span class="text-sm text-red-700 font-medium">💡 Precio sugerido:</span>
                    <span class="font-bold text-red-900">
                        ${{ number_format($aprobacion->precio_sugerido_alternativo, 2) }}
                    </span>
                    <span class="text-sm text-red-600">
                        ({{ number_format((($aprobacion->precio_sugerido_alternativo - $aprobacion->precio_propuesto) / $aprobacion->precio_propuesto) * 100, 2) }}%)
                    </span>
                </div>
            @endif

            <!-- Comentarios -->
            @if ($aprobacion->comentarios)
                <div class="mt-3 p-3 bg-white rounded border border-gray-200">
                    <div class="text-sm font-medium text-gray-700 mb-1">💬 Comentarios:</div>
                    <p class="text-sm text-gray-600">{{ $aprobacion->comentarios }}</p>
                </div>
            @endif

            <!-- Aprobador -->
            @if ($aprobacion->aprobador)
                <div class="mt-2 text-xs text-gray-500">
                    Por: {{ $aprobacion->aprobador->name }}
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-500">
            <p>⏳ Aún no hay retroalimentación de las áreas</p>
        </div>
    @endforelse
</div>
