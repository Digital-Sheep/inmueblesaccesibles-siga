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
    ];

    protected $casts = [
        'nivel_prioridad'    => NivelPrioridadJuicioEnum::class,
        'tipo_proceso'       => TipoProcesoJuicioEnum::class,
        'sede'               => SedeJuicioEnum::class,
        'hay_cesion_derechos' => 'boolean',
        'sin_demanda'        => 'boolean',
        'activo'             => 'boolean',
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
     * Etiqueta para mostrar en el título del recurso.
     */
    public function getTituloAttribute(): string
    {
        return $this->id_garantia
            ?? $this->numero_credito
            ?? "Juicio #{$this->id}";
    }

    /**
     * Última actuación registrada (la más reciente).
     */
    public function getUltimaActuacionAttribute(): ?ActuacionJuicio
    {
        return $this->actuaciones->first();
    }
}
