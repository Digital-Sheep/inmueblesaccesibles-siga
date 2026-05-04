<?php

namespace App\Livewire\Juridico;

use App\Enums\MetodoPagoGastoEnum;
use App\Enums\TipoDocumentoGastoEnum;
use App\Models\Gasto;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class GastosContabilidadComponent extends Component
{
    use WithFileUploads;

    // ── Props ──────────────────────────────────────────────────────────────────
    public string $modelType;
    public int    $modelId;
    public string $pathBase;       // juridico/juicios/GAR-XXXX
    public string $permisoEditar;

    // ── Estado del modal de registro ──────────────────────────────────────────
    public bool   $mostrarModal    = false;
    public bool   $mostrarEliminar = false;
    public ?int   $gastoEliminarId = null;

    // ── Campos del formulario ─────────────────────────────────────────────────
    public string  $concepto       = '';
    public string  $tipoDocumento  = '';
    public string  $metodoPago     = '';
    public ?string $monto          = null;
    public string  $fechaPago      = '';
    public ?string $descripcion    = null;
    public         $comprobante    = null;

    // ── Ciclo de vida ──────────────────────────────────────────────────────────

    public function mount(
        string $modelType,
        int    $modelId,
        string $pathBase,
        string $permisoEditar,
    ): void {
        $this->modelType    = $modelType;
        $this->modelId      = $modelId;
        $this->pathBase     = $pathBase;
        $this->permisoEditar = $permisoEditar;
        $this->fechaPago    = now()->format('Y-m-d');
    }

    // ── Queries ────────────────────────────────────────────────────────────────

    public function getGastos()
    {
        return Gasto::where('gastable_type', $this->modelType)
            ->where('gastable_id', $this->modelId)
            ->orderByDesc('fecha_pago')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getTotalGastos(): string
    {
        $total = Gasto::where('gastable_type', $this->modelType)
            ->where('gastable_id', $this->modelId)
            ->sum('monto');

        return '$' . number_format((float) $total, 2);
    }

    // ── Modal ──────────────────────────────────────────────────────────────────

    public function abrirModal(): void
    {
        $this->verificarPermiso();
        $this->resetFormulario();
        $this->mostrarModal = true;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->concepto      = '';
        $this->tipoDocumento = '';
        $this->metodoPago    = '';
        $this->monto         = null;
        $this->fechaPago     = now()->format('Y-m-d');
        $this->descripcion   = null;
        $this->comprobante   = null;
        $this->resetValidation();
    }

    // ── Guardar ────────────────────────────────────────────────────────────────

    public function guardarGasto(): void
    {
        $this->verificarPermiso();

        $this->validate([
            'concepto'      => ['required', 'string', 'max:200'],
            'tipoDocumento' => ['required', 'in:' . implode(',', array_column(TipoDocumentoGastoEnum::cases(), 'value'))],
            'metodoPago'    => ['required', 'in:' . implode(',', array_column(MetodoPagoGastoEnum::cases(), 'value'))],
            'monto'         => ['required', 'numeric', 'min:0.01'],
            'fechaPago'     => ['required', 'date'],
            'descripcion'   => ['nullable', 'string', 'max:1000'],
            'comprobante'   => ['nullable', 'file', 'max:102400', 'mimes:pdf,jpg,jpeg,png,webp'],
        ], [
            'concepto.required'      => 'El concepto es obligatorio.',
            'tipoDocumento.required' => 'Selecciona el tipo de documento.',
            'metodoPago.required'    => 'Selecciona el método de pago.',
            'monto.required'         => 'El monto es obligatorio.',
            'monto.min'              => 'El monto debe ser mayor a cero.',
            'fechaPago.required'     => 'La fecha de pago es obligatoria.',
            'comprobante.max'        => 'El comprobante no puede pesar más de 100 MB.',
            'comprobante.mimes'      => 'Solo se permiten PDF, JPG, PNG o WEBP.',
        ]);

        try {
            $comprobantePath  = null;
            $comprobanteNombre = null;

            // Subir comprobante si se proporcionó
            if ($this->comprobante) {
                $nombreOriginal   = $this->comprobante->getClientOriginalName();
                $nombreSanitizado = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $nombreOriginal);
                $comprobantePath  = $this->pathBase
                    . '/gastos/'
                    . now()->timestamp . '_' . $nombreSanitizado;

                Storage::disk('private')->putFileAs(
                    dirname($comprobantePath),
                    $this->comprobante->getRealPath(),
                    basename($comprobantePath)
                );

                $comprobanteNombre = $nombreOriginal;
            }

            Gasto::create([
                'gastable_type'               => $this->modelType,
                'gastable_id'                 => $this->modelId,
                'tipo_documento'              => $this->tipoDocumento,
                'concepto'                    => $this->concepto,
                'monto'                       => $this->monto,
                'metodo_pago'                 => $this->metodoPago,
                'fecha_pago'                  => $this->fechaPago,
                'comprobante_path'            => $comprobantePath,
                'comprobante_nombre_original' => $comprobanteNombre,
                'descripcion'                 => $this->descripcion ?: null,
                'created_by'                  => Auth::id(),
                'updated_by'                  => Auth::id(),
            ]);

            $this->cerrarModal();

            Notification::make()
                ->success()
                ->title('Gasto registrado')
                ->send();

            Log::info('[GastosContabilidad] Gasto registrado', [
                'modelo'    => $this->modelType,
                'modelo_id' => $this->modelId,
                'concepto'  => $this->concepto,
                'monto'     => $this->monto,
                'usuario'   => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[GastosContabilidad] Error al registrar gasto', [
                'error'     => $e->getMessage(),
                'modelo'    => $this->modelType,
                'modelo_id' => $this->modelId,
            ]);

            Notification::make()
                ->danger()
                ->title('Error al registrar')
                ->body('Ocurrió un error al guardar el gasto.')
                ->send();
        }
    }

    // ── Descarga comprobante ───────────────────────────────────────────────────

    public function descargarComprobante(int $gastoId): void
    {
        $gasto = $this->encontrarGastoAutorizado($gastoId);

        if (! $gasto || ! $gasto->comprobante_path) {
            return;
        }

        try {
            $url = Storage::disk('private')->temporaryUrl(
                $gasto->comprobante_path,
                now()->addMinutes(30)
            );

            // Despachar evento al JS para abrir en nueva pestaña
            $this->dispatch('abrir-url', url: $url);
        } catch (\Throwable $e) {
            Log::warning('[GastosContabilidad] Error al generar URL comprobante', [
                'gasto_id' => $gastoId,
                'error'    => $e->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error')
                ->body('No se pudo generar el enlace de descarga.')
                ->send();
        }
    }

    // ── Eliminación ────────────────────────────────────────────────────────────

    public function confirmarEliminar(int $gastoId): void
    {
        $this->verificarPermiso();
        $this->gastoEliminarId = $gastoId;
        $this->mostrarEliminar = true;
    }

    public function cancelarEliminar(): void
    {
        $this->gastoEliminarId = null;
        $this->mostrarEliminar = false;
    }

    public function eliminarGasto(): void
    {
        if (! $this->gastoEliminarId) {
            return;
        }

        $this->verificarPermiso();

        $gasto = $this->encontrarGastoAutorizado($this->gastoEliminarId);

        if (! $gasto) {
            $this->cancelarEliminar();
            return;
        }

        try {
            // Eliminar comprobante del disco si existe
            if ($gasto->comprobante_path && Storage::disk('private')->exists($gasto->comprobante_path)) {
                Storage::disk('private')->delete($gasto->comprobante_path);
            }

            $gasto->delete();

            $this->cancelarEliminar();

            Notification::make()
                ->success()
                ->title('Gasto eliminado')
                ->send();
        } catch (\Throwable $e) {
            Log::error('[GastosContabilidad] Error al eliminar gasto', [
                'gasto_id' => $this->gastoEliminarId,
                'error'    => $e->getMessage(),
            ]);

            $this->cancelarEliminar();

            Notification::make()
                ->danger()
                ->title('Error al eliminar')
                ->body('Ocurrió un error al eliminar el gasto.')
                ->send();
        }
    }

    // ── Helpers privados ───────────────────────────────────────────────────────

    private function encontrarGastoAutorizado(int $gastoId): ?Gasto
    {
        $gasto = Gasto::where('id', $gastoId)
            ->where('gastable_type', $this->modelType)
            ->where('gastable_id', $this->modelId)
            ->first();

        if (! $gasto) {
            Log::warning('[GastosContabilidad] Intento de acceso a gasto no autorizado', [
                'gasto_id' => $gastoId,
                'usuario'  => Auth::id(),
            ]);
        }

        return $gasto;
    }

    private function verificarPermiso(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->can($this->permisoEditar)) {
            abort(403, 'No tienes permiso para gestionar gastos de este seguimiento.');
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.juridico.gastos-contabilidad', [
            'gastos'        => $this->getGastos(),
            'totalGastos'   => $this->getTotalGastos(),
            'tiposDoc'      => TipoDocumentoGastoEnum::cases(),
            'metodosPago'   => MetodoPagoGastoEnum::cases(),
            'permisoEditar' => $this->permisoEditar,
        ]);
    }
}
