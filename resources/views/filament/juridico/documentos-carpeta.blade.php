{{--
    Vista: resources/views/filament/juridico/documentos-carpeta.blade.php
    Usada por ViewEntry dentro de los Infolists de seguimientos jurídicos.
    Recibe $record (el seguimiento) y las variables $carpetaId, $carpetaSlug, $permisoEditar
    inyectadas via ->extraAttributes() en el ViewEntry.
    Estilos: solo inline — sin clases Tailwind ni componentes x-heroicon.
--}}

{{-- DEBUG temporal — quitar después
@dump($carpetaId, $carpetaSlug, $permisoEditar) --}}

@livewire(
    'juridico.documentos-carpeta',
    [
        'modelType'     => get_class($record),
        'modelId'       => $record->id,
        'carpetaId'     => $carpetaId,
        'carpetaSlug'   => $carpetaSlug,
        'pathBase'      => $record->path_base,
        'permisoEditar' => $permisoEditar,
    ],
    key('carpeta-' . $carpetaId . '-' . $record->id)
)
