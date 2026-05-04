{{--
    Vista: resources/views/livewire/juridico/gastos-contabilidad.blade.php
    Componente: App\Livewire\Juridico\GastosContabilidadComponent
    Estilos: solo inline — sin clases Tailwind ni componentes x-heroicon
--}}
<div style="padding: 0.5rem 0;">

    {{-- ── Confirmación de eliminación ────────────────────────────────────── --}}
    @if ($mostrarEliminar)
        <div
            style="
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
                ¿Confirmar eliminación del gasto? Esta acción no se puede deshacer.
            </span>
            <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                <button wire:click="eliminarGasto" wire:loading.attr="disabled"
                    style="background-color:#dc2626;color:white;border:none;border-radius:5px;padding:0.375rem 0.875rem;font-size:0.8125rem;font-weight:500;cursor:pointer;">
                    Eliminar
                </button>
                <button wire:click="cancelarEliminar"
                    style="background-color:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:5px;padding:0.375rem 0.875rem;font-size:0.8125rem;font-weight:500;cursor:pointer;">
                    Cancelar
                </button>
            </div>
        </div>
    @endif

    {{-- ── Header: total + botón registrar ───────────────────────────────── --}}
    <div
        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">

        {{-- Total acumulado --}}
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.8125rem; color: #6b7280;">Total registrado:</span>
            <span style="font-size: 1rem; font-weight: 700; color: #111827;">{{ $totalGastos }}</span>
        </div>

        {{-- Botón registrar --}}
        @can($permisoEditar)
            <button wire:click="abrirModal"
                style="display:inline-flex;align-items:center;gap:0.375rem;background-color:#2563eb;color:white;border:none;border-radius:6px;padding:0.5rem 1rem;font-size:0.875rem;font-weight:500;cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Registrar gasto
            </button>
        @endcan
    </div>

    {{-- ── Lista de gastos ─────────────────────────────────────────────────── --}}
    @if ($gastos->isEmpty())
        <div style="text-align:center;padding:2.5rem 1rem;color:#9ca3af;">
            <svg xmlns="http://www.w3.org/2000/svg"
                style="width:40px;height:40px;margin:0 auto 0.75rem;display:block;opacity:0.4;" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
            </svg>
            <p style="font-size:0.875rem;margin:0;">No hay gastos registrados en contabilidad.</p>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            @foreach ($gastos as $gasto)
                <div
                    style="
                    background-color: #f9fafb;
                    border: 1px solid #e5e7eb;
                    border-radius: 6px;
                    padding: 0.75rem 0.875rem;
                ">
                    {{-- Fila principal --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;">

                        {{-- Info del gasto --}}
                        <div style="min-width:0;flex:1;">
                            <div
                                style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.25rem;">

                                {{-- Tipo documento badge --}}
                                <span
                                    style="
                                    font-size: 0.6875rem;
                                    font-weight: 600;
                                    padding: 0.125rem 0.5rem;
                                    border-radius: 9999px;
                                    background-color: #dbeafe;
                                    color: #1e40af;
                                ">{{ $gasto->tipo_documento->getLabel() }}</span>

                                {{-- Método de pago badge --}}
                                <span
                                    style="
                                    font-size: 0.6875rem;
                                    font-weight: 600;
                                    padding: 0.125rem 0.5rem;
                                    border-radius: 9999px;
                                    background-color: #d1fae5;
                                    color: #065f46;
                                ">{{ $gasto->metodo_pago->getLabel() }}</span>

                                {{-- Fecha --}}
                                <span style="font-size:0.75rem;color:#6b7280;">
                                    {{ $gasto->fecha_pago->format('d/m/Y') }}
                                </span>
                            </div>

                            {{-- Concepto --}}
                            <p style="font-size:0.875rem;font-weight:500;color:#111827;margin:0 0 0.125rem;">
                                {{ $gasto->concepto }}
                            </p>

                            {{-- Descripción si existe --}}
                            @if ($gasto->descripcion)
                                <p style="font-size:0.75rem;color:#6b7280;margin:0;">
                                    {{ $gasto->descripcion }}
                                </p>
                            @endif

                            {{-- Registrado por --}}
                            <p style="font-size:0.6875rem;color:#9ca3af;margin:0.25rem 0 0;">
                                Registrado por {{ $gasto->createdBy?->name ?? '—' }}
                                · {{ $gasto->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        {{-- Monto + acciones --}}
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.5rem;flex-shrink:0;">
                            <span style="font-size:1rem;font-weight:700;color:#111827;">
                                {{ $gasto->monto_formateado }}
                            </span>

                            <div style="display:flex;gap:0.375rem;">
                                {{-- Descargar comprobante --}}
                                @if ($gasto->comprobante_path)
                                    <button wire:click="descargarComprobante({{ $gasto->id }})"
                                        wire:loading.attr="disabled"
                                        title="Descargar comprobante: {{ $gasto->comprobante_nombre_original }}"
                                        style="display:inline-flex;align-items:center;justify-content:center;background:none;border:1px solid #d1d5db;border-radius:5px;padding:0.25rem 0.5rem;cursor:pointer;color:#374151;">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                    </button>
                                @endif

                                {{-- Eliminar --}}
                                @can($permisoEditar)
                                    <button wire:click="confirmarEliminar({{ $gasto->id }})" title="Eliminar gasto"
                                        style="display:inline-flex;align-items:center;justify-content:center;background:none;border:1px solid #fca5a5;border-radius:5px;padding:0.25rem 0.5rem;cursor:pointer;color:#dc2626;">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Modal registro de gasto ─────────────────────────────────────────── --}}
    @if ($mostrarModal)
        {{-- Overlay --}}
        <div wire:click="cerrarModal" style="position:fixed;inset:0;background-color:rgba(0,0,0,0.5);z-index:50;"></div>

        {{-- Modal --}}
        <div
            style="
            position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
            background-color:white;border-radius:10px;padding:1.5rem;
            width:90%;max-width:540px;z-index:51;
            box-shadow:0 20px 60px rgba(0,0,0,0.2);
            max-height:90vh;overflow-y:auto;
        ">
            {{-- Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h3 style="font-size:1rem;font-weight:600;color:#111827;margin:0;">
                    Registrar gasto
                </h3>
                <button wire:click="cerrarModal"
                    style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:0.25rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Concepto --}}
            <div style="margin-bottom:0.875rem;">
                <label style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                    Concepto <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" wire:model="concepto"
                    placeholder="Ej: Honorarios abogado, Gestoría RPPC, Arancel notarial"
                    style="display:block;width:100%;font-size:0.875rem;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem 0.75rem;box-sizing:border-box;" />
                @error('concepto')
                    <p style="font-size:0.75rem;color:#dc2626;margin:0.25rem 0 0;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tipo documento + Método de pago --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:0.875rem;">

                <div>
                    <label
                        style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                        Tipo de documento <span style="color:#dc2626;">*</span>
                    </label>
                    <select wire:model="tipoDocumento"
                        style="display:block;width:100%;font-size:0.875rem;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem 0.75rem;box-sizing:border-box;background:white;">
                        <option value="">Seleccionar...</option>
                        @foreach ($tiposDoc as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->getLabel() }}</option>
                        @endforeach
                    </select>
                    @error('tipoDocumento')
                        <p style="font-size:0.75rem;color:#dc2626;margin:0.25rem 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label
                        style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                        Método de pago <span style="color:#dc2626;">*</span>
                    </label>
                    <select wire:model="metodoPago"
                        style="display:block;width:100%;font-size:0.875rem;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem 0.75rem;box-sizing:border-box;background:white;">
                        <option value="">Seleccionar...</option>
                        @foreach ($metodosPago as $metodo)
                            <option value="{{ $metodo->value }}">{{ $metodo->getLabel() }}</option>
                        @endforeach
                    </select>
                    @error('metodoPago')
                        <p style="font-size:0.75rem;color:#dc2626;margin:0.25rem 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Monto + Fecha --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:0.875rem;">
                <div>
                    <label
                        style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                        Monto <span style="color:#dc2626;">*</span>
                    </label>
                    <div style="position:relative;">
                        <span
                            style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:#6b7280;font-size:0.875rem;">$</span>
                        <input type="number" step="0.01" min="0" wire:model="monto" placeholder="0.00"
                            style="display:block;width:100%;font-size:0.875rem;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem 0.75rem 0.5rem 1.5rem;box-sizing:border-box;" />
                    </div>
                    @error('monto')
                        <p style="font-size:0.75rem;color:#dc2626;margin:0.25rem 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label
                        style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                        Fecha de pago <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="date" wire:model="fechaPago"
                        style="display:block;width:100%;font-size:0.875rem;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem 0.75rem;box-sizing:border-box;" />
                    @error('fechaPago')
                        <p style="font-size:0.75rem;color:#dc2626;margin:0.25rem 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Comprobante --}}
            <div style="margin-bottom:0.875rem;">
                <label style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                    Comprobante <span style="color:#9ca3af;font-weight:400;">(opcional)</span>
                </label>
                <input type="file" wire:model="comprobante" accept=".pdf,.jpg,.jpeg,.png,.webp"
                    style="display:block;width:100%;font-size:0.875rem;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem;cursor:pointer;box-sizing:border-box;" />
                <p style="font-size:0.75rem;color:#9ca3af;margin:0.25rem 0 0;">PDF, JPG, PNG o WEBP · Máx. 100 MB</p>
                @error('comprobante')
                    <p style="font-size:0.75rem;color:#dc2626;margin:0.25rem 0 0;">{{ $message }}</p>
                @enderror
                <div wire:loading wire:target="comprobante"
                    style="font-size:0.75rem;color:#6b7280;margin-top:0.25rem;">
                    Subiendo...
                </div>
            </div>

            {{-- Descripción --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.875rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                    Notas adicionales <span style="color:#9ca3af;font-weight:400;">(opcional)</span>
                </label>
                <textarea wire:model="descripcion" rows="2" placeholder="Observaciones del gasto..."
                    style="display:block;width:100%;font-size:0.875rem;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem 0.75rem;box-sizing:border-box;resize:vertical;"></textarea>
            </div>

            {{-- Acciones --}}
            <div style="display:flex;justify-content:flex-end;gap:0.625rem;">
                <button wire:click="cerrarModal"
                    style="background-color:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:0.5rem 1rem;font-size:0.875rem;font-weight:500;cursor:pointer;">
                    Cancelar
                </button>
                <button wire:click="guardarGasto" wire:loading.attr="disabled"
                    style="background-color:#2563eb;color:white;border:none;border-radius:6px;padding:0.5rem 1rem;font-size:0.875rem;font-weight:500;cursor:pointer;">
                    <span wire:loading.remove wire:target="guardarGasto">Guardar gasto</span>
                    <span wire:loading wire:target="guardarGasto">Guardando...</span>
                </button>
            </div>
        </div>
    @endif

    <div style="padding: 0.5rem 0;" x-data="{}"
        @abrir-url.window="window.open($event.detail.url, '_blank')">
    </div>
