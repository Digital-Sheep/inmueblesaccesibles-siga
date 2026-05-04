<?php

namespace App\Models;

use App\Enums\MetodoPagoGastoEnum;
use App\Enums\TipoDocumentoGastoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Gasto extends Model
{
    use SoftDeletes;

    protected $table = 'gastos';

    protected $fillable = [
        'gastable_type',
        'gastable_id',
        'tipo_documento',
        'concepto',
        'monto',
        'metodo_pago',
        'fecha_pago',
        'comprobante_path',
        'comprobante_nombre_original',
        'descripcion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'monto'        => 'decimal:2',
        'fecha_pago'   => 'date',
        'tipo_documento' => TipoDocumentoGastoEnum::class,
        'metodo_pago'    => MetodoPagoGastoEnum::class,
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * El registro al que pertenece este gasto.
     * Hoy: SeguimientoJuicio | SeguimientoNotaria | SeguimientoDictamen
     */
    public function gastable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * URL temporal firmada para descargar el comprobante desde disco private.
     * Válida 30 minutos. NULL si no hay comprobante.
     */
    public function getUrlComprobanteAttribute(): ?string
    {
        if (! $this->comprobante_path) {
            return null;
        }

        try {
            return Storage::disk('private')->temporaryUrl(
                $this->comprobante_path,
                now()->addMinutes(30)
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Monto formateado para mostrar en UI.
     */
    public function getMontoFormateadoAttribute(): string
    {
        return '$' . number_format((float) $this->monto, 2);
    }
}
