{{-- resources/views/filament/validaciones/porcentajes-esquema-pago.blade.php --}}

<div style="background: {{ $esValido ? '#f0fdf4' : '#fef2f2' }}; border-left: 4px solid {{ $esValido ? '#059669' : '#dc2626' }}; border-radius: 8px; padding: 12px; margin-top: 12px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 600; color: {{ $esValido ? '#15803d' : '#b91c1c' }};">
            @if($esValido) ✅ @else ⚠️ @endif Total de Porcentajes
        </span>
        <div style="text-align: right;">
            <div style="font-size: 1.5rem; font-weight: 700; color: {{ $esValido ? '#15803d' : '#b91c1c' }};">
                {{ number_format($total, 2) }}%
            </div>
            <div style="font-size: 0.875rem; color: {{ $esValido ? '#16a34a' : '#dc2626' }};">
                {{ $esValido ? 'Suma correcta' : 'La suma debe ser exactamente 100%' }}
            </div>
        </div>
    </div>
</div>
