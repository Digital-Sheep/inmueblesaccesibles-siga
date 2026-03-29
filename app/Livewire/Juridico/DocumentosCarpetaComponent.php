<?php

namespace App\Livewire\Juridico;

use App\Models\Archivo;
use App\Models\CatCarpetaJuridica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Filament\Notifications\Notification;

class DocumentosCarpetaComponent extends Component
{
    use WithFileUploads;

    // ── Props recibidas desde el ViewEntry ─────────────────────────────────────

    /** Clase completa del modelo, ej: App\Models\SeguimientoJuicio */
    public string $modelType;

    /** ID del registro */
    public int $modelId;

    /** ID de CatCarpetaJuridica */
    public int $carpetaId;

    /** Slug de la carpeta, ej: "docs-administradora" */
    public string $carpetaSlug;

    /**
     * Path base del seguimiento, ej: "juridico/juicios/GAR-974132099"
     * Calculado en el modelo via getPathBaseAttribute()
     */
    public string $pathBase;

    // ── Estado del componente ──────────────────────────────────────────────────

    /** Controla visibilidad del modal de subida */
    public bool $mostrarModal = false;

    /** Archivo temporal de Livewire (antes de guardar) */
    public $archivoSubir = null;

    /** Descripción opcional del documento */
    public string $descripcionSubir = '';

    /** ID del archivo a eliminar (para confirmación) */
    public ?int $archivoEliminarId = null;

    public string $permisoEditar = '';

    // ── Ciclo de vida ──────────────────────────────────────────────────────────

    public function mount(
        string $modelType,
        int    $modelId,
        int    $carpetaId,
        string $carpetaSlug,
        string $pathBase,
    ): void {
        $this->modelType   = $modelType;
        $this->modelId     = $modelId;
        $this->carpetaId   = $carpetaId;
        $this->carpetaSlug = $carpetaSlug;
        $this->pathBase    = $pathBase;
    }

    // ── Queries ────────────────────────────────────────────────────────────────

    /**
     * Documentos de esta carpeta para este seguimiento.
     * Método normal (no #[Computed]) para evitar el bug de caché en producción.
     */
    public function getDocumentos()
    {
        return Archivo::query()
            ->where('entidad_type', $this->modelType)
            ->where('entidad_id', $this->modelId)
            ->where('cat_carpeta_id', $this->carpetaId)
            ->orderByDesc('created_at')
            ->get();
    }

    // ── Upload ─────────────────────────────────────────────────────────────────

    public function abrirModal(): void
    {
        $this->verificarPermiso();
        $this->mostrarModal  = true;
        $this->archivoSubir  = null;
        $this->descripcionSubir = '';
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal  = false;
        $this->archivoSubir  = null;
        $this->descripcionSubir = '';
    }

    public function guardarDocumento(): void
    {
        $this->verificarPermiso();

        $this->validate([
            'archivoSubir' => ['required', 'file', 'max:102400', 'mimes:pdf,jpg,jpeg,png,webp'],
        ], [
            'archivoSubir.required' => 'Debes seleccionar un archivo.',
            'archivoSubir.max'      => 'El archivo no puede pesar más de 100 MB.',
            'archivoSubir.mimes'    => 'Solo se permiten PDF, JPG, PNG o WEBP.',
        ]);

        try {
            $archivo      = $this->archivoSubir;
            $nombreOriginal = $archivo->getClientOriginalName();
            $mime         = $archivo->getMimeType();
            $pesoKb       = (int) ceil($archivo->getSize() / 1024);

            // Path: juridico/juicios/GAR-XXXX/documentos/docs-administradora/1743000000_contrato.pdf
            $nombreSanitizado = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $nombreOriginal);
            $rutaArchivo = $this->pathBase
                . '/documentos/'
                . $this->carpetaSlug
                . '/' . now()->timestamp . '_' . $nombreSanitizado;

            // Guardar en disco private
            Storage::disk('private')->putFileAs(
                dirname($rutaArchivo),
                $archivo->getRealPath(),
                basename($rutaArchivo)
            );

            // Crear registro en BD
            Archivo::create([
                'entidad_type'    => $this->modelType,
                'entidad_id'      => $this->modelId,
                'categoria'       => 'DOCUMENTO_JURIDICO',
                'cat_carpeta_id'  => $this->carpetaId,
                'nombre_original' => $nombreOriginal,
                'ruta_archivo'    => $rutaArchivo,
                'tipo_mime'       => $mime,
                'peso_kb'         => $pesoKb,
                'descripcion'     => $this->descripcionSubir ?: null,
                'subido_por_id'   => Auth::id(),
                'created_by'      => Auth::id(),
                'updated_by'      => Auth::id(),
            ]);

            $this->cerrarModal();

            Notification::make()
                ->success()
                ->title('Documento guardado')
                ->send();

            Log::info('[DocumentosCarpeta] Archivo subido', [
                'modelo'    => $this->modelType,
                'modelo_id' => $this->modelId,
                'carpeta'   => $this->carpetaSlug,
                'archivo'   => $nombreOriginal,
                'usuario'   => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[DocumentosCarpeta] Error al subir archivo', [
                'error'     => $e->getMessage(),
                'modelo'    => $this->modelType,
                'modelo_id' => $this->modelId,
                'carpeta'   => $this->carpetaSlug,
            ]);

            Notification::make()
                ->danger()
                ->title('Error al guardar')
                ->body('Ocurrió un error al guardar el archivo.')
                ->send();
        }
    }

    // ── Descarga ───────────────────────────────────────────────────────────────

    public function descargarDocumento(int $archivoId): mixed
    {
        $archivo = $this->encontrarArchivoAutorizado($archivoId, soloLectura: true);

        if (! $archivo) {
            return null;
        }

        try {
            // URL temporal firmada — válida 30 minutos
            $url = Storage::disk('private')->temporaryUrl(
                $archivo->ruta_archivo,
                now()->addMinutes(30)
            );

            return $this->redirect($url);
        } catch (\Throwable $e) {
            Log::warning('[DocumentosCarpeta] Error al generar URL temporal', [
                'archivo_id' => $archivoId,
                'error'      => $e->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error al generar enlace')
                ->body('Ocurrió un error al generar el enlace de descarga.')
                ->send();

            return null;
        }
    }

    // ── Eliminación ────────────────────────────────────────────────────────────

    public function confirmarEliminar(int $archivoId): void
    {
        $this->verificarPermiso();
        $this->archivoEliminarId = $archivoId;
    }

    public function cancelarEliminar(): void
    {
        $this->archivoEliminarId = null;
    }

    public function eliminarDocumento(): void
    {
        if (! $this->archivoEliminarId) {
            return;
        }

        $this->verificarPermiso();

        $archivo = $this->encontrarArchivoAutorizado($this->archivoEliminarId, soloLectura: false);

        if (! $archivo) {
            $this->archivoEliminarId = null;
            return;
        }

        try {
            // Eliminar del disco
            if (Storage::disk('private')->exists($archivo->ruta_archivo)) {
                Storage::disk('private')->delete($archivo->ruta_archivo);
            } else {
                Log::warning('[DocumentosCarpeta] Archivo físico no encontrado al eliminar', [
                    'archivo_id'   => $archivo->id,
                    'ruta_archivo' => $archivo->ruta_archivo,
                ]);
            }

            // Soft delete del registro
            $archivo->delete();

            $this->archivoEliminarId = null;

            session()->flash('doc_exito_' . $this->carpetaId, 'Documento eliminado.');

            Log::info('[DocumentosCarpeta] Archivo eliminado', [
                'archivo_id' => $archivo->id,
                'usuario'    => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[DocumentosCarpeta] Error al eliminar archivo', [
                'archivo_id' => $this->archivoEliminarId,
                'error'      => $e->getMessage(),
            ]);

            $this->archivoEliminarId = null;

            Notification::make()
                ->success()
                ->title('Documento eliminado')
                ->send();
        }
    }

    // ── Helpers privados ───────────────────────────────────────────────────────

    /**
     * Busca un archivo verificando que pertenezca a ESTE seguimiento y carpeta.
     * Evita que alguien manipule el ID para acceder a archivos de otro registro.
     */
    private function encontrarArchivoAutorizado(int $archivoId, bool $soloLectura): ?Archivo
    {
        $archivo = Archivo::where('id', $archivoId)
            ->where('entidad_type', $this->modelType)
            ->where('entidad_id', $this->modelId)
            ->where('cat_carpeta_id', $this->carpetaId)
            ->first();

        if (! $archivo) {
            Log::warning('[DocumentosCarpeta] Intento de acceso a archivo no autorizado', [
                'archivo_id' => $archivoId,
                'usuario'    => Auth::id(),
            ]);

            return null;
        }

        return $archivo;
    }

    /**
     * Verifica permiso de escritura según el tipo de modelo.
     * Lanza excepción si no tiene permiso.
     */
    private function verificarPermiso(): void
    {
        $permisos = [
            \App\Models\SeguimientoJuicio::class    => 'seguimientojuicios_editar',
            \App\Models\SeguimientoNotaria::class   => 'seguimientonotarias_editar',
            \App\Models\SeguimientoDictamen::class  => 'seguimientodictamenes_editar',
        ];

        $permiso = $permisos[$this->modelType] ?? null;

        if (! $permiso || ! Auth::user()->can($permiso)) {
            abort(403, 'No tienes permiso para modificar documentos de este seguimiento.');
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.juridico.documentos-carpeta', [
            'documentos'    => $this->getDocumentos(),
            'permisoEditar' => $this->permisoEditar,
            'carpetaId'     => $this->carpetaId,
        ]);
    }
}
