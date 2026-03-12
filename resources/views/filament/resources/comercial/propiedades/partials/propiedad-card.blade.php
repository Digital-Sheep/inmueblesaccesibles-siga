{{-- Partial: propiedad-card.blade.php
     Uso: @include('filament.resources.comercial.propiedades.partials.propiedad-card', ['propiedad' => $propiedad])
--}}
@php
    use Illuminate\Support\Facades\Storage;
    use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;

    $foto    = $propiedad->archivos->firstWhere('categoria', 'FACHADA') ?? $propiedad->archivos->first();
    $fotoUrl = $foto ? Storage::url($foto->ruta_archivo) : null;

    $badgeConfig = match($propiedad->estatus_comercial) {
        'DISPONIBLE'  => ['bg' => '#10b981', 'label' => 'Disponible'],
        'EN_INTERES'  => ['bg' => '#3b82f6', 'label' => 'En Interés'],
        'EN_PROCESO'  => ['bg' => '#f59e0b', 'label' => 'Apartada'],
        'VENDIDA'     => ['bg' => '#ef4444', 'label' => 'Vendida'],
        'EN_REVISION' => ['bg' => '#6366f1', 'label' => 'En Revisión'],
        default       => ['bg' => '#9ca3af', 'label' => $propiedad->estatus_comercial],
    };

    $precio = $propiedad->precio_venta_con_descuento ?? $propiedad->precio_venta_sugerido;
    $url    = PropiedadResource::getUrl('view', ['record' => $propiedad->id]);
    $municipio = $propiedad->municipio?->nombre;
    $sucursal  = $propiedad->sucursal?->nombre;
    $ubicacion = collect([$municipio, $sucursal])->filter()->implode(' · ');
@endphp

<a
    href="{{ $url }}"
    wire:navigate
    style="display:flex; flex-direction:column; background:#fff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb; box-shadow:0 1px 3px rgba(0,0,0,.06); text-decoration:none; transition:box-shadow .2s, transform .2s;"
    onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'; this.style.transform='translateY(-2px)';"
    onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.06)'; this.style.transform='translateY(0)';"
>
    {{-- Imagen --}}
    <div style="position:relative; height:180px; background:#f3f4f6; overflow:hidden; flex-shrink:0;">
        @if($fotoUrl)
            <img
                src="{{ $fotoUrl }}"
                alt="{{ $propiedad->direccion_completa }}"
                loading="lazy"
                style="width:100%; height:100%; object-fit:cover;"
            />
        @else
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; gap:8px; color:#d1d5db;">
                <svg style="width:48px;height:48px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span style="font-size:12px; color:#9ca3af;">Sin foto</span>
            </div>
        @endif

        {{-- Badge estatus --}}
        <div style="position:absolute; top:10px; left:10px;">
            <span style="display:inline-block; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:600; color:#fff; background:{{ $badgeConfig['bg'] }}; box-shadow:0 1px 3px rgba(0,0,0,.2);">
                {{ $badgeConfig['label'] }}
            </span>
        </div>

        {{-- Badge tipo --}}
        @if($propiedad->tipo_inmueble)
            <div style="position:absolute; top:10px; right:10px;">
                <span style="display:inline-block; padding:3px 8px; border-radius:999px; font-size:11px; font-weight:500; color:#fff; background:rgba(0,0,0,.45); backdrop-filter:blur(4px);">
                    {{ $propiedad->tipo_inmueble }}
                </span>
            </div>
        @endif
    </div>

    {{-- Contenido --}}
    <div style="display:flex; flex-direction:column; flex:1; padding:14px 16px; gap:6px;">

        {{-- Número crédito --}}
        <p style="font-size:11px; font-family:monospace; color:#9ca3af; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            {{ $propiedad->numero_credito ?? 'Sin folio' }}
        </p>

        {{-- Dirección --}}
        <p style="font-size:13px; font-weight:600; color:#111827; margin:0; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
            {{ $propiedad->direccion_completa }}
        </p>

        {{-- Ubicación --}}
        @if($ubicacion)
            <p style="font-size:12px; color:#6b7280; margin:0; display:flex; align-items:center; gap:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ $ubicacion }}
            </p>
        @endif

        {{-- Características --}}
        @if($propiedad->habitaciones || $propiedad->banos || $propiedad->construccion_m2)
            <div style="display:flex; align-items:center; gap:12px; font-size:12px; color:#6b7280; padding-top:8px; border-top:1px solid #f3f4f6;">
                @if($propiedad->construccion_m2)
                    <span>📐 {{ number_format($propiedad->construccion_m2, 0) }}m²</span>
                @endif
                @if($propiedad->habitaciones)
                    <span>🛏 {{ $propiedad->habitaciones }}</span>
                @endif
                @if($propiedad->banos)
                    <span>🚿 {{ $propiedad->banos }}</span>
                @endif
            </div>
        @endif

        {{-- Precio --}}
        <div style="margin-top:auto; padding-top:8px;">
            @if($precio)
                <p style="font-size:17px; font-weight:700; color:#2563eb; margin:0;">
                    ${{ number_format($precio, 0) }}
                    <span style="font-size:11px; font-weight:400; color:#9ca3af;">MXN</span>
                </p>
            @else
                <p style="font-size:13px; color:#9ca3af; font-style:italic; margin:0;">Precio no publicado</p>
            @endif
        </div>
    </div>
</a>
