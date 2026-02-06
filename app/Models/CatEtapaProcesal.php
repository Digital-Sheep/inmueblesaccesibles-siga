<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatEtapaProcesal extends Model
{
    use SoftDeletes;

    protected $table = 'cat_etapas_procesales';

    protected $fillable = [
        'nombre',
        'tipo_juicio_id',
        'dias_termino_legal',
        'orden',
        'fase_cotizacion',
        'porcentaje_inversion',
        'aplica_para_cotizacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'porcentaje_inversion' => 'decimal:2',
        'aplica_para_cotizacion' => 'boolean',
    ];

    // --- RELACIONES ---

    public function tipoJuicio(): BelongsTo
    {
        return $this->belongsTo(CatTipoJuicio::class, 'tipo_juicio_id');
    }

    /**
     * Esta etapa se usa en muchos seguimientos reales.
     */
    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoJuridico::class, 'etapa_id');
    }

    // --- AUDITORÍA ---

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --- SCOPES ---

    /**
     * Solo etapas que aplican para cotización
     */
    public function scopeParaCotizacion($query)
    {
        return $query->where('aplica_para_cotizacion', true)
            ->where('activo', true)
            ->orderBy('orden');
    }

    /**
     * Etapas por fase
     */
    public function scopePorFase($query, string $fase)
    {
        return $query->where('fase_cotizacion', $fase);
    }

    /**
     * Obtener etapas para el selector del cotizador
     */
    public static function getOpcionesParaCotizador(): array
    {
        return self::paraCotizacion()
            ->get()
            ->mapWithKeys(function ($etapa) {
                return [
                    $etapa->id => sprintf(
                        '%s (%s - %s%%)',
                        $etapa->nombre,
                        $etapa->fase_cotizacion,
                        $etapa->porcentaje_inversion
                    )
                ];
            })
            ->toArray();
    }

    /**
     * Obtener porcentaje de inversión de una etapa
     */
    public static function getPorcentajeInversion(int $etapaId): ?float
    {
        $etapa = self::find($etapaId);
        return $etapa?->porcentaje_inversion;
    }

    /**
     * Verificar si la etapa aplica para cotización
     */
    public function aplicaParaCotizacion(): bool
    {
        return $this->aplica_para_cotizacion === true;
    }

    /**
     * Obtener badge de fase
     */
    public function getBadgeFaseAttribute(): array
    {
        return match ($this->fase_cotizacion) {
            'FASE_1' => ['color' => 'info', 'label' => 'Fase 1 - 35%'],
            'FASE_2' => ['color' => 'success', 'label' => 'Fase 2 - 20%'],
            'FASE_3' => ['color' => 'warning', 'label' => 'Fase 3 - 15%'],
            default => ['color' => 'gray', 'label' => 'Sin fase'],
        };
    }
}
