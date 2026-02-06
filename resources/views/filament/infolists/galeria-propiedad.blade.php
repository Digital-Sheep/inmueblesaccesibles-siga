<div>
    @if ($getRecord()->archivos && $getRecord()->archivos->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
            @foreach ($getRecord()->archivos as $archivo)
                @php
                    // Generar URL correcta según el disk configurado
                    $disk = config('filament.default_filesystem_disk', 'public');
                    $imageUrl = $archivo->ruta_archivo
                        ? \Illuminate\Support\Facades\Storage::disk($disk)->url($archivo->ruta_archivo)
                        : null;
                @endphp

                <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: white;">
                    @if ($imageUrl)
                        <a href="{{ $imageUrl }}" target="_blank" style="display: block;">
                            <img src="{{ $imageUrl }}" alt="{{ $archivo->descripcion ?? 'Foto de propiedad' }}"
                                style="width: 100%; height: 200px; object-fit: cover; cursor: pointer; display: block;" />
                        </a>
                    @else
                        <div
                            style="width: 100%; height: 200px; background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                            <span style="color: #9ca3af;">Sin imagen</span>
                        </div>
                    @endif

                    @if ($archivo->descripcion)
                        <div style="padding: 12px;">
                            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">
                                {{ $archivo->descripcion }}
                            </p>
                        </div>
                    @endif

                    @if ($archivo->tipo_archivo)
                        <div style="padding: 0 12px 12px 12px;">
                            <span
                                style="display: inline-block; padding: 4px 8px; background: #e0e7ff; color: #3730a3; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">
                                {{ strtoupper($archivo->tipo_archivo) }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 40px; color: #9ca3af;">
            <svg style="width: 64px; height: 64px; margin: 0 auto 16px; opacity: 0.3;" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                </path>
            </svg>
            <p style="margin: 0; font-size: 1rem;">No hay fotos disponibles</p>
        </div>
    @endif
</div>
