<x-filament-panels::page>

    {{-- Toggle + Filtros --}}
    <div
        style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:12px; margin-bottom:24px;">

        <div
            style="display:inline-flex; border-radius:8px; border:1px solid #e5e7eb; overflow:hidden; box-shadow:0 1px 2px rgba(0,0,0,.05);">
            <button wire:click="cambiarVista('tabla')"
                style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; font-size:13px; font-weight:500; border:none; cursor:pointer;
                    background:{{ $this->vista === 'tabla' ? '#4f46e5' : '#ffffff' }};
                    color:{{ $this->vista === 'tabla' ? '#ffffff' : '#6b7280' }};">
                <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                </svg>
                Lista
            </button>
            <button wire:click="cambiarVista('cards')"
                style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; font-size:13px; font-weight:500; border:none; border-left:1px solid #e5e7eb; border-right:1px solid #e5e7eb; cursor:pointer;
                    background:{{ $this->vista === 'cards' ? '#4f46e5' : '#ffffff' }};
                    color:{{ $this->vista === 'cards' ? '#ffffff' : '#6b7280' }};">
                <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Catálogo
            </button>
            <button wire:click="cambiarVista('mapa')"
                style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; font-size:13px; font-weight:500; border:none; cursor:pointer;
                    background:{{ $this->vista === 'mapa' ? '#4f46e5' : '#ffffff' }};
                    color:{{ $this->vista === 'mapa' ? '#ffffff' : '#6b7280' }};">
                <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Mapa
            </button>
        </div>

        @if ($this->vista !== 'tabla')
            <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">

                {{-- Filtro Estatus --}}
                <x-filament::input.wrapper style="min-width:180px;">
                    <x-filament::input.select wire:model.live="filtroEstatus">
                        <option value="">Todos los estatus</option>
                        <option value="DISPONIBLE">✅ Disponible</option>
                        <option value="EN_INTERES">👀 En Interés</option>
                        <option value="EN_PROCESO">🔒 Apartada</option>
                        <option value="VENDIDA">🏷️ Vendida</option>
                        <option value="EN_REVISION">🔍 En Revisión</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                {{-- Filtro Tipo --}}
                <x-filament::input.wrapper style="min-width:180px;">
                    <x-filament::input.select wire:model.live="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="CASA">🏠 Casa</option>
                        <option value="DEPARTAMENTO">🏢 Departamento</option>
                        <option value="TERRENO">🌿 Terreno</option>
                        <option value="LOCAL">🏪 Local</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                {{-- Filtro Sucursal --}}
                <x-filament::input.wrapper style="min-width:180px;">
                    <x-filament::input.select wire:model.live="filtroSucursal">
                        <option value="0">Todas las sucursales</option>
                        @foreach ($this->sucursales as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

            </div>
        @endif
    </div>

    {{-- VISTA: TABLA --}}
    @if ($this->vista === 'tabla')
        {{ $this->table }}
    @endif

    {{-- VISTA: CARDS --}}
    @if ($this->vista === 'cards')
        @php $propiedades = $this->getPropiedades(); @endphp

        {{-- DEBUG TEMPORAL --}}
        @if (app()->environment('production') || true)
            <div
                style="background:#fef3c7; border:1px solid #f59e0b; border-radius:6px; padding:12px; margin-bottom:16px; font-size:12px; font-family:monospace;">
                filtroEstatus: "{{ $this->filtroEstatus }}" |
                filtroTipo: "{{ $this->filtroTipo }}" |
                filtroSucursal: {{ $this->filtroSucursal }} |
                total: {{ $propiedades->total() }}
            </div>
        @endif

        @if ($propiedades->isEmpty())
            <div
                style="background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:48px; text-align:center;">
                <div style="font-size:48px; margin-bottom:12px;">🏘️</div>
                <p style="font-size:16px; font-weight:600; color:#111827; margin:0 0 4px;">Sin propiedades</p>
                <p style="font-size:14px; color:#6b7280; margin:0;">Ajusta los filtros para ver resultados.</p>
            </div>
        @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px;">
                @foreach ($propiedades as $propiedad)
                    @include('filament.resources.comercial.propiedades.partials.propiedad-card', [
                        'propiedad' => $propiedad,
                    ])
                @endforeach
            </div>
            <div
                style="margin-top:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <p style="font-size:13px; color:#6b7280; margin:0;">
                    Mostrando {{ $propiedades->firstItem() }}–{{ $propiedades->lastItem() }} de
                    {{ $propiedades->total() }} propiedades
                </p>
                <div style="display:flex; gap:8px; align-items:center;">
                    @if ($propiedades->onFirstPage())
                        <span
                            style="padding:6px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:13px; color:#d1d5db;">←
                            Anterior</span>
                    @else
                        <button wire:click="previousPage"
                            style="padding:6px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:13px; color:#374151; background:#fff; cursor:pointer;">←
                            Anterior</button>
                    @endif

                    <span style="font-size:13px; color:#6b7280;">Página {{ $propiedades->currentPage() }} de
                        {{ $propiedades->lastPage() }}</span>

                    @if ($propiedades->hasMorePages())
                        <button wire:click="nextPage"
                            style="padding:6px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:13px; color:#374151; background:#fff; cursor:pointer;">Siguiente
                            →</button>
                    @else
                        <span
                            style="padding:6px 12px; border:1px solid #e5e7eb; border-radius:6px; font-size:13px; color:#d1d5db;">Siguiente
                            →</span>
                    @endif
                </div>
            </div>
        @endif
    @endif

    {{-- VISTA: MAPA --}}
    @if ($this->vista === 'mapa')
        @php $marcadores = $this->propiedadesParaMapa; @endphp

        <div style="background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden;"
            x-data="mapaInmuebles" x-init="inicializar(@js($marcadores))"
            @vista-mapa-activada.window="inicializar(@js($marcadores))">
            <div
                style="padding:12px 16px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between;">
                <p style="font-size:13px; font-weight:500; color:#374151; margin:0;">
                    {{ count($marcadores) }} propiedad(es) con ubicación registrada
                </p>
                @if (count($marcadores) === 0)
                    <span style="font-size:12px; color:#d97706;">
                        ⚠️ Ninguna propiedad tiene coordenadas asignadas con estos filtros. Edita las propiedades para
                        agregar latitud y longitud.
                    </span>
                @endif
            </div>

            <div id="mapa-propiedades" style="width:100%; height:600px;"></div>

            <div style="padding:12px 16px; border-top:1px solid #e5e7eb; display:flex; flex-wrap:wrap; gap:16px;">
                @foreach ([['#10b981', 'Disponible'], ['#3b82f6', 'En Interés'], ['#f59e0b', 'Apartada'], ['#ef4444', 'Vendida']] as [$color, $label])
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:12px; height:12px; border-radius:50%; background:{{ $color }};"></div>
                        <span style="font-size:12px; color:#6b7280;">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================================================================
         Leaflet + Alpine component — SIEMPRE presentes (fuera de @if)
         para que Alpine los encuentre al cambiar de vista
    ================================================================ --}}
    @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endassets

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mapaInmuebles', () => ({
                mapa: null,
                capas: [],
                colores: {
                    DISPONIBLE: '#10b981',
                    EN_INTERES: '#3b82f6',
                    EN_PROCESO: '#f59e0b',
                    VENDIDA: '#ef4444',
                    EN_REVISION: '#6366f1',
                    default: '#6b7280',
                },

                inicializar(propiedades) {
                    // Esperar a que Livewire termine de renderizar el div
                    this.$nextTick(() => {
                        const el = document.getElementById('mapa-propiedades');
                        if (!el) return;

                        if (this.mapa) {
                            this.mapa.remove();
                            this.mapa = null;
                        }

                        this.mapa = L.map('mapa-propiedades').setView([20.6597, -103.3496], 11);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© <a href="https://www.openstreetmap.org">OpenStreetMap</a>',
                            maxZoom: 19,
                        }).addTo(this.mapa);

                        this.capas = [];
                        propiedades.forEach(p => this.agregarPin(p));

                        if (this.capas.length > 0) {
                            this.mapa.fitBounds(
                                L.featureGroup(this.capas).getBounds().pad(0.1)
                            );
                        }
                    });
                },

                agregarPin(prop) {
                    const color = this.colores[prop.estatus] ?? this.colores.default;

                    const icono = L.divIcon({
                        className: '',
                        html: `<div style="width:28px;height:28px;background:${color};border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 6px rgba(0,0,0,.25);"></div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 28],
                        popupAnchor: [0, -30],
                    });

                    const precio = prop.precio ?
                        '$' + new Intl.NumberFormat('es-MX').format(prop.precio) :
                        null;

                    const popup = `
                        <div style="min-width:260px;font-family:system-ui,sans-serif;">
                            ${prop.imagen ? `<img src="/storage/${prop.imagen}" style="width:100%;height:140px;object-fit:cover;display:block;">` : ''}
                            <div style="padding:12px;">
                                <p style="font-size:11px;color:#9ca3af;font-family:monospace;margin:0 0 2px;">${prop.numero_credito ?? ''}</p>
                                <p style="font-size:13px;font-weight:600;margin:0 0 6px;line-height:1.3;color:#111827;">${prop.direccion}</p>
                                ${precio ? `<p style="font-size:18px;font-weight:700;color:#2563eb;margin:0 0 8px;">${precio}</p>` : ''}
                                <div style="display:flex;gap:12px;font-size:12px;color:#6b7280;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid #e5e7eb;">
                                    ${prop.construccion ? `<span>📐 ${prop.construccion}m²</span>` : ''}
                                    ${prop.habitaciones ? `<span>🛏 ${prop.habitaciones}</span>` : ''}
                                    ${prop.banos       ? `<span>🚿 ${prop.banos}</span>`        : ''}
                                </div>
                                <a href="${prop.url}" style="display:block;text-align:center;background:#2563eb;color:white;padding:8px;border-radius:6px;font-size:13px;font-weight:500;text-decoration:none;">
                                    Ver detalles →
                                </a>
                            </div>
                        </div>`;

                    const marker = L.marker([prop.lat, prop.lng], {
                            icon: icono
                        })
                        .bindPopup(popup, {
                            maxWidth: 280
                        })
                        .addTo(this.mapa);

                    this.capas.push(marker);
                },
            }));
        });
    </script>

    <style>
        .leaflet-popup-content-wrapper {
            padding: 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .leaflet-popup-content {
            margin: 0;
        }
    </style>

</x-filament-panels::page>
