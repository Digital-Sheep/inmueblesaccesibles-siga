{{--
    Vista: resources/views/livewire/juridico/documentos-carpeta.blade.php
    Componente: App\Livewire\Juridico\DocumentosCarpetaComponent
    Estilos: solo inline — sin clases Tailwind ni componentes x-heroicon
--}}
<div style="padding: 0.5rem 0;">

    {{-- ── Mensajes de estado ─────────────────────────────────────────────── --}}
    @if(session('doc_exito_' . $carpetaId))
        <div style="
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            color: #166534;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('doc_exito_' . $carpetaId) }}
        </div>
    @endif

    @if(session('doc_error_' . $carpetaId))
        <div style="
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            color: #991b1b;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            {{ session('doc_error_' . $carpetaId) }}
        </div>
    @endif

    {{-- ── Confirmación de eliminación ────────────────────────────────────── --}}
    @if($archivoEliminarId)
        <div style="
            background-color: #fff7ed;
            border: 1px solid #fdba74;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        ">
            <span style="color: #9a3412; font-size: 0.875rem; font-weight: 500;">
                ¿Confirmar eliminación? Esta acción no se puede deshacer.
            </span>
            <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                <button
                    wire:click="eliminarDocumento"
                    wire:loading.attr="disabled"
                    style="
                        background-color: #dc2626;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        padding: 0.375rem 0.875rem;
                        font-size: 0.8125rem;
                        font-weight: 500;
                        cursor: pointer;
                    "
                >
                    Eliminar
                </button>
                <button
                    wire:click="cancelarEliminar"
                    style="
                        background-color: #f3f4f6;
                        color: #374151;
                        border: 1px solid #d1d5db;
                        border-radius: 5px;
                        padding: 0.375rem 0.875rem;
                        font-size: 0.8125rem;
                        font-weight: 500;
                        cursor: pointer;
                    "
                >
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    {{-- ── Botón agregar ───────────────────────────────────────────────────── --}}
    @can($permisoEditar)
        <div style="margin-bottom: 1rem; text-align: right;">
            <button
                wire:click="abrirModal"
                style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.375rem;
                    background-color: #4f39f6;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    padding: 0.5rem 1rem;
                    font-size: 0.875rem;
                    font-weight: 500;
                    cursor: pointer;
                "
            >
                Agregar documento
            </button>
        </div>
    @endcan

    {{-- ── Lista de documentos ─────────────────────────────────────────────── --}}
    @if($documentos->isEmpty())
        <div style="
            text-align: center;
            padding: 2.5rem 1rem;
            color: #9ca3af;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;margin:0 auto 0.75rem;display:block;opacity:0.4;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
            </svg>
            <p style="font-size: 0.875rem; margin: 0;">No hay documentos en esta carpeta.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach($documentos as $doc)
                <div style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    background-color: #f9fafb;
                    border: 1px solid #e5e7eb;
                    border-radius: 6px;
                    padding: 0.625rem 0.875rem;
                    gap: 1rem;
                ">
                    {{-- Ícono + nombre --}}
                    <div style="display: flex; align-items: center; gap: 0.625rem; min-width: 0; flex: 1;">
                        {{-- Ícono según mime --}}
                        @if(str_contains($doc->tipo_mime, 'pdf'))
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;flex-shrink:0;color:#dc2626;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;flex-shrink:0;color:#6366f1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 9h18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z" />
                            </svg>
                        @endif

                        <div style="min-width: 0;">
                            <p style="
                                font-size: 0.875rem;
                                font-weight: 500;
                                color: #111827;
                                margin: 0;
                                white-space: nowrap;
                                overflow: hidden;
                                text-overflow: ellipsis;
                            ">{{ $doc->nombre_original }}</p>
                            <p style="font-size: 0.75rem; color: #6b7280; margin: 0.125rem 0 0;">
                                {{ $doc->peso_legible }} &middot; {{ $doc->created_at->format('d/m/Y H:i') }}
                                @if($doc->descripcion)
                                    &middot; {{ $doc->descripcion }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div style="display: flex; align-items: center; gap: 0.375rem; flex-shrink: 0;">
                        {{-- Descargar --}}
                        <button
                            wire:click="descargarDocumento({{ $doc->id }})"
                            wire:loading.attr="disabled"
                            title="Descargar"
                            style="
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                background: none;
                                border: 1px solid #d1d5db;
                                border-radius: 5px;
                                padding: 0.25rem 0.5rem;
                                cursor: pointer;
                                color: #374151;
                            "
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </button>

                        {{-- Eliminar --}}
                        @can($permisoEditar)
                            <button
                                wire:click="confirmarEliminar({{ $doc->id }})"
                                title="Eliminar"
                                style="
                                    display: inline-flex;
                                    align-items: center;
                                    justify-content: center;
                                    background: none;
                                    border: 1px solid #fca5a5;
                                    border-radius: 5px;
                                    padding: 0.25rem 0.5rem;
                                    cursor: pointer;
                                    color: #dc2626;
                                "
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Modal de subida ─────────────────────────────────────────────────── --}}
    @if($mostrarModal)
        {{-- Overlay --}}
        <div
            wire:click="cerrarModal"
            style="
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 50;
            "
        ></div>

        {{-- Modal --}}
        <div style="
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            width: 90%;
            max-width: 480px;
            z-index: 51;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        ">
            {{-- Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1rem; font-weight: 600; color: #111827; margin: 0;">
                    Agregar documento
                </h3>
                <button
                    wire:click="cerrarModal"
                    style="background: none; border: none; cursor: pointer; color: #9ca3af; padding: 0.25rem;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Campo: archivo --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem;">
                    Archivo <span style="color: #dc2626;">*</span>
                </label>
                <input
                    type="file"
                    wire:model="archivoSubir"
                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                    style="
                        display: block;
                        width: 100%;
                        font-size: 0.875rem;
                        color: #374151;
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        padding: 0.5rem;
                        cursor: pointer;
                        box-sizing: border-box;
                    "
                />
                <p style="font-size: 0.75rem; color: #9ca3af; margin: 0.25rem 0 0;">
                    PDF, JPG, PNG o WEBP · Máx. 100 MB
                </p>
                @error('archivoSubir')
                    <p style="font-size: 0.75rem; color: #dc2626; margin: 0.25rem 0 0;">{{ $message }}</p>
                @enderror

                {{-- Loading indicator de Livewire --}}
                <div wire:loading wire:target="archivoSubir" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    Subiendo...
                </div>
            </div>

            {{-- Campo: descripción --}}
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem;">
                    Descripción <span style="color: #9ca3af; font-weight: 400;">(opcional)</span>
                </label>
                <input
                    type="text"
                    wire:model="descripcionSubir"
                    placeholder="Ej: Estado de cuenta enero 2026"
                    style="
                        display: block;
                        width: 100%;
                        font-size: 0.875rem;
                        color: #374151;
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        padding: 0.5rem 0.75rem;
                        box-sizing: border-box;
                    "
                />
            </div>

            {{-- Acciones --}}
            <div style="display: flex; justify-content: flex-end; gap: 0.625rem;">
                <button
                    wire:click="cerrarModal"
                    style="
                        background-color: #f3f4f6;
                        color: #374151;
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        padding: 0.5rem 1rem;
                        font-size: 0.875rem;
                        font-weight: 500;
                        cursor: pointer;
                    "
                >
                    Cancelar
                </button>
                <button
                    wire:click="guardarDocumento"
                    wire:loading.attr="disabled"
                    style="
                        background-color: #4f39f6;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        padding: 0.5rem 1rem;
                        font-size: 0.875rem;
                        font-weight: 500;
                        cursor: pointer;
                    "
                >
                    <span wire:loading.remove wire:target="guardarDocumento">Guardar</span>
                    <span wire:loading wire:target="guardarDocumento">Guardando...</span>
                </button>
            </div>
        </div>
    @endif

</div>
