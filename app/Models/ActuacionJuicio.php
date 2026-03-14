<?php

namespace App\Models;

use App\Enums\EstatusAvanceEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ActuacionJuicio extends Model
{
    protected $table = 'actuaciones_juicio';

    protected $fillable = [
        'seguimiento_juicio_id',
        'fecha_actuacion',
        'descripcion_actuacion',
        'archivo_evidencia',
        'hubo_avance',
        'semana_label',
    ];

    protected $casts = [
        'fecha_actuacion' => 'date',
        'hubo_avance'     => EstatusAvanceEnum::class,
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function seguimientoJuicio(): BelongsTo
    {
        return $this->belongsTo(SeguimientoJuicio::class);
    }

    // ── Helpers de archivo ─────────────────────────────────────────────────────

    /**
     * Devuelve la URL temporal del archivo en el disco 'private'.
     * Usar en Infolist para generar link de descarga seguro.
     */
    public function getUrlArchivoAttribute(): ?string
    {
        if (! $this->archivo_evidencia) {
            return null;
        }

        return Storage::disk('private')->temporaryUrl(
            $this->archivo_evidencia,
            now()->addMinutes(30)
        );
    }

    /**
     * Devuelve solo el nombre del archivo para mostrar en tabla.
     */
    public function getNombreArchivoAttribute(): ?string
    {
        if (! $this->archivo_evidencia) {
            return null;
        }

        return basename($this->archivo_evidencia);
    }

    // ── Directorio de almacenamiento ───────────────────────────────────────────

    /**
     * Genera el directorio de almacenamiento para este juicio.
     * Estructura: juridico/juicios/{id_garantia}/actuaciones/{año}-{mes}
     *
     * Llamar desde FileUpload::make()->directory(fn() => ActuacionJuicio::directorioParaJuicio(...))
     */
    public static function directorioParaJuicio(string $idGarantia): string
    {
        return 'juridico/juicios/' . $idGarantia . '/actuaciones/' . now()->format('Y-m');
    }
}
