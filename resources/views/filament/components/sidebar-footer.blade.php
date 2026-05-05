@php
    $url = \App\Filament\Pages\SimuladorComercialPage::getUrl();
    $isActive = request()->routeIs('filament.admin.pages.simulador-comercial');
@endphp

<div style="padding: 12px 16px; border-top: 1px solid rgba(0,0,0,0.06);">
    <a href="{{ $url }}" wire:navigate
        style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; {{ $isActive ? 'background: rgba(99,102,241,0.1); color: #4f46e5;' : 'color: #6b7280;' }}">
        <svg style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
        </svg>
        <span x-show="$store.sidebar.isOpen" x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start" x-transition:enter-end="fi-transition-enter-end"
            style="white-space: nowrap; overflow: hidden;">Simulador Comercial</span>
    </a>
</div>
