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
        'fecha_proxima_actuacion',
        'descripcion_actuacion',
        'etapa_actual',
        'archivo_evidencia',
        'hubo_avance',
        'semana_label',
    ];

    protected $casts = [
        'fecha_actuacion'          => 'date',
        'fecha_proxima_actuacion'  => 'date',
        'hubo_avance'              => EstatusAvanceEnum::class,
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function seguimientoJuicio(): BelongsTo
    {
        return $this->belongsTo(SeguimientoJuicio::class);
    }

    // ── Helpers de archivo ─────────────────────────────────────────────────────

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

    public function getNombreArchivoAttribute(): ?string
    {
        if (! $this->archivo_evidencia) {
            return null;
        }

        return basename($this->archivo_evidencia);
    }

    // ── Directorio de almacenamiento ───────────────────────────────────────────

    public static function directorioParaJuicio(string $idGarantia): string
    {
        return 'juridico/juicios/' . $idGarantia . '/actuaciones/' . now()->format('Y-m');
    }
}
