<?php

namespace App\Models;

use App\Enums\EstatusAvanceEnum;
use App\Enums\NivelPrioridadJuicioEnum;
use App\Enums\SedeJuicioEnum;
use App\Enums\TipoProcesoJuicioEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeguimientoJuicio extends Model
{
    use SoftDeletes;

    protected $table = 'seguimientos_juicio';

    protected $fillable = [
        'propiedad_id',
        'numero_credito',
        'id_garantia',
        'nombre_cliente',
        'administradora',
        'domicilio',
        'sede',
        'nivel_prioridad',
        'tipo_proceso',
        'abogado_nombre',
        'actor',
        'demandado',
        'numero_expediente',
        'juzgado',
        'distrito_judicial',
        'tipo_juicio_materia',
        'via_procesal',
        'hay_cesion_derechos',
        'cedente',
        'cesionario',
        'etapa_actual',
        'estrategia_juridica',
        'notas_director',
        'sin_demanda',
        'activo',
        'ultima_actuacion_at',
    ];

    protected $casts = [
        'nivel_prioridad'     => NivelPrioridadJuicioEnum::class,
        'tipo_proceso'        => TipoProcesoJuicioEnum::class,
        'sede'                => SedeJuicioEnum::class,
        'hay_cesion_derechos' => 'boolean',
        'sin_demanda'         => 'boolean',
        'activo'              => 'boolean',
        'ultima_actuacion_at' => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Propiedad vinculada en SIGA (opcional en v1).
     * Null-safe siempre: puede no existir todavía.
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    /**
     * Actuaciones semanales del juicio.
     * Ordenadas de más reciente a más antigua para facilitar la vista.
     */
    public function actuaciones(): HasMany
    {
        return $this->hasMany(ActuacionJuicio::class)
                    ->orderByDesc('fecha_actuacion');
    }

    // ── Accessors de utilidad ──────────────────────────────────────────────────

    /**
     * Etiqueta descriptiva para breadcrumb y recordTitleAttribute.
     * Formato: "[SEDE] - [Cliente]" con fallbacks progresivos.
     */
    public function getTituloAttribute(): string
    {
        $sede   = $this->sede instanceof SedeJuicioEnum ? $this->sede->getLabel() : null;
        $partes = array_filter([$sede, $this->nombre_cliente ?? $this->id_garantia ?? $this->numero_credito]);

        return ! empty($partes)
            ? implode(' — ', $partes)
            : "Juicio #{$this->id}";
    }

    /**
     * Días transcurridos desde la última actuación registrada.
     * Null si nunca ha tenido actuaciones.
     */
    public function getDiasSinActuacionAttribute(): ?int
    {
        if (! $this->ultima_actuacion_at) {
            return null;
        }

        return (int) $this->ultima_actuacion_at->diffInDays(now());
    }

    /**
     * Indica si el juicio está rezagado (>7 días sin actuación).
     */
    public function getEstaRezagadoAttribute(): bool
    {
        if (! $this->ultima_actuacion_at) {
            return true; // Sin ninguna actuación = rezagado
        }

        return $this->ultima_actuacion_at->diffInDays(now()) > 7;
    }

    /**
     * Última actuación registrada (la más reciente).
     */
    public function getUltimaActuacionAttribute(): ?ActuacionJuicio
    {
        return $this->actuaciones->first();
    }
}
