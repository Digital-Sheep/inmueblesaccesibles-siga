<x-filament-panels::page>
    {{--
        Contenedor full-height que ocupa todo el espacio disponible
        El iframe carga el HTML con su propio CSS y JS sin interferir con Filament
    --}}
    <div
        style="
        width: 100%;
        height: calc(100vh - 120px);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    ">
        <iframe src="{{ asset('simulador/index.html') }}"
            style="
                width: 100%;
                height: 100%;
                border: none;
                display: block;
            "
            title="Simulador Comercial" allowfullscreen></iframe>
    </div>
</x-filament-panels::page>
