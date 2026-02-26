{{-- resources/views/filament/previews/cotizacion-preview.blade.php --}}

@php
    $precioBase = $record->precio_lista ?? 0;
    $porcentajeInversion = $etapa->porcentaje_inversion;

    $costoRemodelacion = $tabulador->costo_remodelacion;
    $costoLuz = $tabulador->costo_luz;
    $costoAgua = $tabulador->costo_agua;
    $costoPredial = $tabulador->costo_predial;
    $costoGastosJuridicos = $tabulador->costo_gastos_juridicos;

    $totalCostos = $costoRemodelacion + $costoLuz + $costoAgua + $costoPredial + $costoGastosJuridicos;
    $costosSinRemodelacion = $costoLuz + $costoAgua + $costoPredial + $costoGastosJuridicos;

    // FÓRMULAS CORRECTAS
    // 1. COSTO TOTAL (primero)
    $costoTotal = $precioBase + $totalCostos;

    // 2. PRECIO VENTA SUGERIDO (garantiza el % de utilidad)
    $precioVentaSugerido = $costoTotal / (1 - ($porcentajeInversion / 100));

    // 3. PRECIO SIN REMODELACIÓN
    $precioSinRemodelacion = ($costoTotal - $costoRemodelacion) / (1 - ($porcentajeInversion / 100));

    // 4. PRECIO CON DESCUENTO
    $precioVentaConDescuento = $precioVentaSugerido * (1 - ($porcentajeDescuento / 100));

    // 5. MONTO DE INVERSIÓN (utilidad sin descuento)
    $montoInversion = $precioVentaSugerido - $costoTotal;

    // 6. UTILIDAD CON DESCUENTO
    $utilidadConDescuento = $precioVentaConDescuento - $costoTotal;

    // 7. PORCENTAJES DE UTILIDAD
    $porcentajeUtilidadSugerido = ($montoInversion / $costoTotal) * 100;
    $porcentajeUtilidadConDescuento = ($utilidadConDescuento / $costoTotal) * 100;
    $utilidadSinRemodelacion = $precioSinRemodelacion - ($costoTotal - $costoRemodelacion);
    $porcentajeUtilidadSinRemo = ($utilidadSinRemodelacion / ($costoTotal - $costoRemodelacion)) * 100;
@endphp

<div style="background: white; border-radius: 8px; border: 1px solid #e5e7eb; padding: 24px;">

    {{-- Precio Base --}}
    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; margin-bottom: 16px;">
        <span style="font-weight: 500; color: #374151;">💵 Precio base (Lista)</span>
        <span style="font-size: 1.125rem; font-weight: 700; color: #111827;">${{ number_format($precioBase, 2) }}</span>
    </div>

    {{-- Costos Desglosados --}}
    <div style="background: #eff6ff; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
        <div style="font-weight: 600; color: #1e40af; margin-bottom: 12px;">📋 Costos operativos</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.875rem; margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #4b5563;">Remodelación:</span>
                <span style="font-weight: 500;">${{ number_format($costoRemodelacion, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #4b5563;">Luz:</span>
                <span style="font-weight: 500;">${{ number_format($costoLuz, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #4b5563;">Agua:</span>
                <span style="font-weight: 500;">${{ number_format($costoAgua, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #4b5563;">Predial:</span>
                <span style="font-weight: 500;">${{ number_format($costoPredial, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #4b5563;">Gastos jurídicos:</span>
                <span style="font-weight: 500;">${{ number_format($costoGastosJuridicos, 2) }}</span>
            </div>
        </div>
        <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #bfdbfe; font-weight: 700;">
            <span>Total costos:</span>
            <span style="color: #1e40af;">${{ number_format($totalCostos, 2) }}</span>
        </div>
    </div>

    {{-- Incremento por Inversión --}}
    <div style="background: #faf5ff; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span style="font-weight: 600; color: #6b21a8;">💎 Incremento por inversión</span>
                <span style="font-size: 0.875rem; color: #9333ea; margin-left: 8px;">({{ $porcentajeInversion }}%)</span>
            </div>
            <span style="font-size: 1.125rem; font-weight: 700; color: #6b21a8;">${{ number_format($montoInversion, 2) }}</span>
        </div>
    </div>

    {{-- Precios Calculados --}}
    <div style="padding-top: 12px; border-top: 2px solid #e5e7eb; margin-bottom: 12px;">

        {{-- Precio Sin Remodelación --}}
        <div style="display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <span style="font-weight: 500; color: #166534;">🏠 Precio sin remodelación</span>
                <span style="background: #86efac; color: #14532d; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                    {{ number_format($porcentajeUtilidadSinRemo, 2) }}% utilidad
                </span>
            </div>
            <span style="font-size: 1.25rem; font-weight: 700; color: #14532d;">${{ number_format($precioSinRemodelacion, 2) }}</span>
        </div>

        {{-- Precio Venta Sugerido --}}
        <div style="display: flex; justify-content: space-between; align-items: center; background: #eff6ff; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <span style="font-weight: 500; color: #1e40af;">✨ Precio venta sugerido</span>
                <span style="background: #93c5fd; color: #1e3a8a; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                    {{ number_format($porcentajeUtilidadSugerido, 2) }}% utilidad
                </span>
            </div>
            <span style="font-size: 1.25rem; font-weight: 700; color: #1e3a8a;">${{ number_format($precioVentaSugerido, 2) }}</span>
        </div>

        {{-- Precio Con Descuento --}}
        <div style="display: flex; justify-content: space-between; align-items: center; background: #fff7ed; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <div>
                    <span style="font-weight: 500; color: #9a3412;">🎯 Precio con descuento</span>
                    <span style="font-size: 0.875rem; color: #ea580c; margin-left: 8px;">(-{{ $porcentajeDescuento }}%)</span>
                </div>
                <span style="background: #fed7aa; color: #78350f; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                    {{ number_format($porcentajeUtilidadConDescuento, 2) }}% utilidad
                </span>
            </div>
            <span style="font-size: 1.5rem; font-weight: 700; color: #7c2d12;">${{ number_format($precioVentaConDescuento, 2) }}</span>
        </div>
    </div>

    {{-- Utilidad Esperada --}}
    <div style="display: flex; justify-content: space-between; align-items: center; background: #fef9c3; border-radius: 8px; padding: 16px; border: 2px solid #fde047;">
        <div>
            <span style="font-size: 1.125rem; font-weight: 700; color: #713f12;">💰 Utilidad esperada</span>
            <span style="font-size: 0.875rem;"> (con descuento)</span>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #713f12;">{{ number_format($porcentajeUtilidadConDescuento, 2) }}%</div>
            <div style="font-size: 0.875rem; color: #854d0e;">${{ number_format($utilidadConDescuento, 2) }}</div>
        </div>
    </div>

</div>

{{-- Comparativos con Valor Comercial --}}
@if($record->precio_valor_comercial && $record->precio_valor_comercial > 0)
    @php
        $valorComercial = $record->precio_valor_comercial;

        // Comparativo 1: Sin Remodelación
        $porcentajeSinRemo = (($valorComercial - $precioSinRemodelacion) / $valorComercial) * 100;
        $esRemateSinRemo = $porcentajeSinRemo >= 35;
        $colorSinRemo = $esRemateSinRemo ? '#059669' : '#dc2626';
        $bgSinRemo = $esRemateSinRemo ? '#f0fdf4' : '#fef2f2';
        $iconoSinRemo = $esRemateSinRemo ? '✅' : '⚠️';

        // Comparativo 2: Precio Sugerido
        $porcentajeSugerido = (($valorComercial - $precioVentaSugerido) / $valorComercial) * 100;
        $esRemateSugerido = $porcentajeSugerido >= 35;
        $colorSugerido = $esRemateSugerido ? '#059669' : '#dc2626';
        $bgSugerido = $esRemateSugerido ? '#f0fdf4' : '#fef2f2';
        $iconoSugerido = $esRemateSugerido ? '✅' : '⚠️';

        // Comparativo 3: Con Descuento
        $porcentajeDesc = (($valorComercial - $precioVentaConDescuento) / $valorComercial) * 100;
        $esRemateDesc = $porcentajeDesc >= 35;
        $colorDesc = $esRemateDesc ? '#059669' : '#dc2626';
        $bgDesc = $esRemateDesc ? '#f0fdf4' : '#fef2f2';
        $iconoDesc = $esRemateDesc ? '✅' : '⚠️';
    @endphp

    <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid #d1d5db;">
        <div style="font-weight: bold; font-size: 1.125rem; color: #111827; margin-bottom: 16px;">🏘️ Comparativo con valor comercial</div>

        <div style="background-color: #dbeafe; border-radius: 8px; padding: 16px; margin-bottom: 16px; text-align: center;">
            <div style="font-size: 0.875rem; color: #1d4ed8; margin-bottom: 4px;">Valor de mercado de referencia</div>
            <div style="font-size: 1.5rem; font-weight: bold; color: #1e3a8a;">${{ number_format($valorComercial, 2) }}</div>
        </div>

        {{-- Comparativo Sin Remodelación --}}
        <div style="background: {{ $bgSinRemo }}; border-left: 4px solid {{ $colorSinRemo }}; border-radius: 4px; padding: 12px; margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;"><span style="font-weight: 600;">Sin remodelación:</span> ${{ number_format($precioSinRemodelacion, 2) }}</div>
                <div style="font-weight: bold; color: {{ $colorSinRemo }};">{{ $iconoSinRemo }} {{ number_format($porcentajeSinRemo, 2) }}% debajo</div>
            </div>
        </div>

        {{-- Comparativo Precio Sugerido --}}
        <div style="background: {{ $bgSugerido }}; border-left: 4px solid {{ $colorSugerido }}; border-radius: 4px; padding: 12px; margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;"><span style="font-weight: 600;">Precio sugerido:</span> ${{ number_format($precioVentaSugerido, 2) }}</div>
                <div style="font-weight: bold; color: {{ $colorSugerido }};">{{ $iconoSugerido }} {{ number_format($porcentajeSugerido, 2) }}% debajo</div>
            </div>
        </div>

        {{-- Comparativo Con Descuento --}}
        <div style="background: {{ $bgDesc }}; border-left: 4px solid {{ $colorDesc }}; border-radius: 4px; padding: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;"><span style="font-weight: 600;">Con descuento:</span> ${{ number_format($precioVentaConDescuento, 2) }}</div>
                <div style="font-weight: bold; color: {{ $colorDesc }};">{{ $iconoDesc }} {{ number_format($porcentajeDesc, 2) }}% debajo</div>
            </div>
        </div>

        <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 4px; padding: 12px; margin-top: 16px; font-size: 0.875rem; color: #92400e;">
            <strong>Nota:</strong> Para considerarse remate, el precio debe estar al menos <strong>35% debajo</strong> del valor comercial de mercado.
        </div>
    </div>
@endif
